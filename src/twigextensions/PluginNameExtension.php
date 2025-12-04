<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\twigextensions;

use lindemannrock\slideshowmanager\SlideshowManager;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Plugin Name Twig Extension
 *
 * @since 1.0.0
 */
class PluginNameExtension extends AbstractExtension implements GlobalsInterface
{
    public function getName(): string
    {
        return 'Slideshow Manager - Plugin Name Helper';
    }
    public function getGlobals(): array
    {
        return ['slideshowHelper' => new PluginNameHelper()];
    }
}

class PluginNameHelper
{
    public function getDisplayName(): string
    {
        return SlideshowManager::$plugin->getSettings()->getDisplayName();
    }
    public function getPluralDisplayName(): string
    {
        return SlideshowManager::$plugin->getSettings()->getPluralDisplayName();
    }
    public function getFullName(): string
    {
        return SlideshowManager::$plugin->getSettings()->getFullName();
    }
    public function getLowerDisplayName(): string
    {
        return SlideshowManager::$plugin->getSettings()->getLowerDisplayName();
    }
    public function getPluralLowerDisplayName(): string
    {
        return SlideshowManager::$plugin->getSettings()->getPluralLowerDisplayName();
    }
    public function __get(string $name): ?string
    {
        $method = 'get' . ucfirst($name);
        return method_exists($this, $method) ? $this->$method() : null;
    }
}
