<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\models;

use Craft;
use craft\base\Model;
use craft\helpers\Template;
use Twig\Markup;

/**
 * Slide Model
 *
 * Represents a single slide in a slideshow
 */
class Slide extends Model implements \JsonSerializable
{
    /**
     * Slide content types
     */
    const TYPE_RICH_TEXT = 'richText';
    const TYPE_IMAGE = 'image';
    const TYPE_ENTRY = 'entry';
    const TYPE_HTML = 'html';

    /**
     * @var string Slide type
     */
    public string $type = self::TYPE_RICH_TEXT;

    /**
     * @var mixed Slide content (varies by type)
     * - richText: HTML string
     * - image: Asset ID
     * - entry: Entry ID
     * - html: HTML string
     */
    public mixed $content = null;

    /**
     * @var array Slide-specific settings
     */
    public array $settings = [];

    /**
     * @var int Slide order/position
     */
    public int $order = 0;

    /**
     * @var string|null Custom CSS classes
     */
    public ?string $cssClass = null;

    /**
     * @var array Additional metadata
     */
    public array $metadata = [];

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): mixed
    {
        return [
            'type' => $this->type,
            'content' => $this->content,
            'settings' => $this->settings,
            'order' => $this->order,
            'cssClass' => $this->cssClass,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['type'], 'required'],
            [['type'], 'in', 'range' => [
                self::TYPE_RICH_TEXT,
                self::TYPE_IMAGE,
                self::TYPE_ENTRY,
                self::TYPE_HTML,
            ]],
            [['content', 'settings', 'metadata'], 'safe'],
            [['order'], 'integer'],
            [['cssClass'], 'string'],
        ];
    }

    /**
     * Get the slide content for rendering
     *
     * @return mixed
     */
    public function getRenderedContent(): mixed
    {
        switch ($this->type) {
            case self::TYPE_RICH_TEXT:
            case self::TYPE_HTML:
                return Template::raw($this->content ?? '');

            case self::TYPE_IMAGE:
                $asset = Craft::$app->getAssets()->getAssetById($this->content);
                return $asset;

            case self::TYPE_ENTRY:
                $entry = Craft::$app->getEntries()->getEntryById($this->content);
                return $entry;

            default:
                return $this->content;
        }
    }

    /**
     * Render the slide
     *
     * @param array $options Rendering options
     * @return Markup
     */
    public function render(array $options = []): Markup
    {
        $content = $this->getRenderedContent();
        $cssClass = $this->cssClass ? ' ' . $this->cssClass : '';

        switch ($this->type) {
            case self::TYPE_IMAGE:
                if ($content) {
                    $html = '<div class="swiper-slide' . $cssClass . '">';
                    $html .= '<img src="' . $content->getUrl() . '" alt="' . ($content->title ?? '') . '">';
                    $html .= '</div>';
                    return Template::raw($html);
                }
                break;

            case self::TYPE_ENTRY:
                if ($content) {
                    $html = '<div class="swiper-slide' . $cssClass . '">';
                    $html .= '<h3>' . $content->title . '</h3>';
                    $html .= '</div>';
                    return Template::raw($html);
                }
                break;

            case self::TYPE_RICH_TEXT:
            case self::TYPE_HTML:
            default:
                $html = '<div class="swiper-slide' . $cssClass . '">';
                $html .= $content;
                $html .= '</div>';
                return Template::raw($html);
        }

        return Template::raw('<div class="swiper-slide' . $cssClass . '"></div>');
    }
}
