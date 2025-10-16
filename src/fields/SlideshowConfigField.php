<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Json;
use lindemannrock\slideshowmanager\SlideshowManager;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * Slideshow Config Field
 *
 * Stores Swiper configuration for slideshows
 */
class SlideshowConfigField extends Field
{
    use LoggingTrait;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('slideshow-manager');
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $plugin = SlideshowManager::getInstance();
        $baseName = $plugin->name ?? 'Slideshow Manager';
        return $baseName . ' Config';
    }

    /**
     * @inheritdoc
     */
    public static function icon(): string
    {
        return '@appicons/sliders.svg';
    }

    /**
     * @inheritdoc
     */
    public static function dbType(): array|string|null
    {
        return 'text';
    }

    /**
     * @inheritdoc
     *
     * Simplified to trust Craft's namespace extraction.
     * Since we use {% namespace %} blocks in the template,
     * Craft automatically extracts field data from POST and passes it here.
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        $plugin = SlideshowManager::getInstance();
        $defaults = $plugin->getSettings()->defaultSwiperConfig ?? [];

        // If it's a string (from database), decode it
        if (is_string($value) && !empty($value)) {
            $decoded = Json::decodeIfJson($value);

            // Check if decoding failed (returns original string on failure)
            if (is_string($decoded) && $decoded === $value) {
                // Only log actual errors (invalid JSON)
                $this->logWarning('Invalid config JSON', [
                    'value' => $value,
                    'elementId' => $element?->id,
                ]);
                return $defaults;
            }

            $value = $decoded;
        }

        // If it's an array (from Craft extraction or database), merge with defaults
        if (is_array($value) && !empty($value)) {
            $result = $defaults;
            foreach ($value as $key => $val) {
                $result[$key] = $val;
            }

            // Don't log normal operations - normalizeValue is called multiple times per request
            return $result;
        }

        // Empty value - return defaults
        // Don't log - this is normal operation
        return $defaults;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return Json::encode($value);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        // Get global default Swiper config
        $plugin = SlideshowManager::getInstance();
        $defaultConfig = $plugin->getSettings()->defaultSwiperConfig ?? [];

        // Pass the simple field handle to the template
        // The template uses {% namespace %} blocks to handle all namespace complexity
        return Craft::$app->getView()->renderTemplate(
            'slideshow-manager/_components/fields/SlideshowConfigField/input',
            [
                'field' => $this,
                'value' => $value,
                'defaultConfig' => $defaultConfig,
                'namespace' => $this->handle,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function includeInGqlSchema(\craft\models\GqlSchema $schema): bool
    {
        return true;
    }
}
