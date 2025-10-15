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
use craft\fields\Matrix;
use craft\helpers\ArrayHelper;
use lindemannrock\slideshowmanager\SlideshowManager;

/**
 * Slideshow Field
 *
 * Simple Matrix field wrapper for managing slides
 * Configuration is handled by the separate SlideshowConfigField
 */
class SlideshowField extends Matrix
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $plugin = SlideshowManager::getInstance();
        return $plugin->name ?? 'Slideshow Manager';
    }

    /**
     * @inheritdoc
     */
    public static function icon(): string
    {
        return '@appicons/photo.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        // Just return the parent Matrix settings
        return parent::getSettingsHtml();
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        // Just return the Matrix field - no config UI
        return parent::getInputHtml($value, $element);
    }
}
