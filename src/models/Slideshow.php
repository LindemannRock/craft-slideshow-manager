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
use craft\helpers\Json;
use craft\helpers\Template;
use lindemannrock\slideshowmanager\SlideshowManager;
use Twig\Markup;

/**
 * Slideshow Model
 *
 * Represents a collection of slides with configuration
 */
class Slideshow extends Model implements \JsonSerializable
{
    /**
     * @var Slide[] Array of slides
     */
    private array $_slides = [];

    /**
     * @var array Swiper configuration
     */
    public array $config = [];

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): mixed
    {
        return [
            'slides' => array_map(fn($slide) => $slide->jsonSerialize(), $this->_slides),
            'config' => $this->config,
        ];
    }

    /**
     * Get all slides
     *
     * @return Slide[]
     */
    public function getSlides(): array
    {
        return $this->_slides;
    }

    /**
     * Set slides
     *
     * @param array $slides
     */
    public function setSlides(array $slides): void
    {
        $this->_slides = [];

        foreach ($slides as $slideData) {
            if ($slideData instanceof Slide) {
                $this->_slides[] = $slideData;
            } elseif (is_array($slideData)) {
                $slide = new Slide($slideData);
                $this->_slides[] = $slide;
            }
        }

        // Sort by order
        usort($this->_slides, fn($a, $b) => $a->order <=> $b->order);
    }

    /**
     * Add a slide
     *
     * @param Slide $slide
     */
    public function addSlide(Slide $slide): void
    {
        $this->_slides[] = $slide;
        usort($this->_slides, fn($a, $b) => $a->order <=> $b->order);
    }

    /**
     * Get slide count
     *
     * @return int
     */
    public function getCount(): int
    {
        return count($this->_slides);
    }

    /**
     * Check if slideshow has slides
     *
     * @return bool
     */
    public function hasSlides(): bool
    {
        return !empty($this->_slides);
    }

    /**
     * Get merged configuration (defaults + field config)
     *
     * @return array
     */
    public function getMergedConfig(): array
    {
        $settings = SlideshowManager::getInstance()->getSettings();
        return array_merge($settings->defaultSwiperConfig, $this->config);
    }

    /**
     * Render the slideshow
     *
     * @param array $options Additional rendering options/config overrides
     * @return Markup
     */
    public function render(array $options = []): Markup
    {
        if (empty($this->_slides)) {
            return Template::raw('');
        }

        // Merge configurations: defaults → field config → template options
        $config = array_merge($this->getMergedConfig(), $options);
        $configJson = Json::htmlEncode($config);

        $html = '<div class="swiper" data-swiper-config=\'' . $configJson . '\'>';
        $html .= '<div class="swiper-wrapper">';

        foreach ($this->_slides as $slide) {
            $html .= $slide->render();
        }

        $html .= '</div>'; // .swiper-wrapper

        // Add navigation if enabled
        if ($config['navigation'] ?? false) {
            $html .= '<div class="swiper-button-prev"></div>';
            $html .= '<div class="swiper-button-next"></div>';
        }

        // Add pagination if enabled
        if (($config['pagination']['enabled'] ?? false) || ($config['pagination'] ?? false)) {
            $html .= '<div class="swiper-pagination"></div>';
        }

        // Add scrollbar if enabled
        if ($config['scrollbar'] ?? false) {
            $html .= '<div class="swiper-scrollbar"></div>';
        }

        $html .= '</div>'; // .swiper

        return Template::raw($html);
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['config'], 'safe'],
        ];
    }
}
