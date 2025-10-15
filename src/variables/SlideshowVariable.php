<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\variables;

use lindemannrock\slideshowmanager\models\Slideshow;
use lindemannrock\slideshowmanager\SlideshowManager;
use Twig\Markup;

/**
 * Slideshow Variable
 *
 * Provides template-level access to slideshow functionality
 */
class SlideshowVariable
{
    /**
     * Render a slideshow
     *
     * @param Slideshow $slideshow
     * @param array $options Additional rendering options
     * @return Markup
     */
    public function render(Slideshow $slideshow, array $options = []): Markup
    {
        return $slideshow->render($options);
    }

    /**
     * Get plugin settings
     *
     * @return \lindemannrock\slideshowmanager\models\Settings
     */
    public function getSettings()
    {
        return SlideshowManager::getInstance()->getSettings();
    }

    /**
     * Get default Swiper configuration
     *
     * @return array
     */
    public function getDefaultConfig(): array
    {
        return $this->getSettings()->defaultSwiperConfig;
    }

    /**
     * Build Swiper configuration from field config
     * Transforms flat field structure into nested Swiper format
     *
     * @param array $config The merged configuration (field + global)
     * @param string $sliderId Unique identifier for this slider instance
     * @return array Swiper-ready configuration
     */
    public function buildSwiperConfig(array $config, string $sliderId): array
    {
        $swiperConfig = [];

        // Simple values - pass through directly
        $simpleValues = [
            'slidesPerView',
            'spaceBetween',
            'loop',
            'speed',
            'effect',
            'centeredSlides',
            'direction',
            'grabCursor',
            'threshold',
            'touchRatio',
            'allowTouchMove',
            'simulateTouch'
        ];

        foreach ($simpleValues as $key) {
            if (isset($config[$key]) && $config[$key] !== '' && $config[$key] !== null) {
                // Convert string "1"/"0" to boolean for boolean fields
                if (in_array($key, ['loop', 'centeredSlides', 'grabCursor', 'allowTouchMove', 'simulateTouch'])) {
                    $swiperConfig[$key] = (bool)$config[$key];
                } else {
                    $swiperConfig[$key] = is_numeric($config[$key]) ? (float)$config[$key] : $config[$key];
                }
            }
        }

        // Navigation
        if (!empty($config['navigation'])) {
            $swiperConfig['navigation'] = [
                'nextEl' => ".swiper-button-next-{$sliderId}",
                'prevEl' => ".swiper-button-prev-{$sliderId}",
            ];
        } else {
            $swiperConfig['navigation'] = false;
        }

        // Pagination
        if (!empty($config['paginationEnabled'])) {
            $swiperConfig['pagination'] = [
                'el' => ".swiper-pagination-{$sliderId}",
                'clickable' => !empty($config['paginationClickable']),
            ];

            if (!empty($config['paginationType'])) {
                $swiperConfig['pagination']['type'] = $config['paginationType'];
            }
        } else {
            $swiperConfig['pagination'] = false;
        }

        // Autoplay
        if (!empty($config['autoplayEnabled'])) {
            $swiperConfig['autoplay'] = [
                'delay' => !empty($config['autoplayDelay']) ? (int)$config['autoplayDelay'] : 3000,
                'disableOnInteraction' => !empty($config['autoplayDisableOnInteraction']),
            ];
        } else {
            $swiperConfig['autoplay'] = false;
        }

        // Grid
        if (!empty($config['gridEnabled'])) {
            $swiperConfig['grid'] = [
                'rows' => !empty($config['gridRows']) ? (int)$config['gridRows'] : 1,
                'fill' => !empty($config['gridFill']) ? $config['gridFill'] : 'row',
            ];
        }

        // Breakpoints - transform from array to keyed object
        if (!empty($config['breakpoints']) && is_array($config['breakpoints'])) {
            $swiperConfig['breakpoints'] = [];
            foreach ($config['breakpoints'] as $breakpoint) {
                $width = (string)($breakpoint['width'] ?? 0);
                $swiperConfig['breakpoints'][$width] = [
                    'slidesPerView' => isset($breakpoint['slidesPerView']) ? (float)$breakpoint['slidesPerView'] : 1,
                    'spaceBetween' => isset($breakpoint['spaceBetween']) ? (int)$breakpoint['spaceBetween'] : 0,
                ];
            }
        }

        // Global module settings - pass through if they exist and are enabled
        $moduleSettings = [
            'keyboard',
            'mousewheel',
            'scrollbar',
            'hashNavigation',
            'freeMode',
            'lazy',
            'parallax',
            'zoom',
            'virtual',
            'a11y'
        ];

        foreach ($moduleSettings as $module) {
            if (isset($config[$module]) && is_array($config[$module]) && !empty($config[$module]['enabled'])) {
                $swiperConfig[$module] = $config[$module];
            }
        }

        return $swiperConfig;
    }

    /**
     * Get CSS classes for navigation/pagination visibility
     *
     * @param string $visibility Visibility option (default, hide-mobile, hide-desktop, mobile-only, desktop-only)
     * @return string CSS classes
     */
    public function getVisibilityClasses(string $visibility): string
    {
        return match($visibility) {
            'hide-mobile' => 'hidden md:block',
            'hide-desktop' => 'block md:hidden',
            'mobile-only' => 'block md:hidden',
            'desktop-only' => 'hidden md:block',
            default => '',
        };
    }

    /**
     * Build inline CSS custom properties for Swiper styling
     * Allows customization via --_swiper-* variables with fallbacks
     *
     * @param array|null $cssVars Optional custom CSS variables (from settings)
     * @return string Inline style string with CSS custom properties
     */
    public function buildCssVars(?array $cssVars = null): string
    {
        // Get CSS vars from settings if not provided
        if ($cssVars === null) {
            $settings = $this->getSettings();
            $cssVars = $settings->swiperCssVars ?? [];
        }

        if (empty($cssVars)) {
            return '';
        }

        $styles = [];

        // Define all available CSS custom properties with their fallbacks
        $varMap = [
            // Theme
            'themeColor' => '--swiper-theme-color:var(--_swiper-theme-color, {value})',

            // Navigation
            'navigationSize' => '--swiper-navigation-size:var(--_swiper-navigation-size, {value})',
            'navigationTopOffset' => '--swiper-navigation-top-offset:var(--_swiper-navigation-top-offset, {value})',
            'navigationSidesOffset' => '--swiper-navigation-sides-offset:var(--_swiper-navigation-sides-offset, {value})',
            'navigationColor' => '--swiper-navigation-color:var(--_swiper-navigation-color, {value})',
            'navigationInactiveColor' => '--swiper-navigation-inactive-color:var(--_swiper-navigation-inactive-color, {value})',
            'navigationBg' => '--swiper-navigation-bg:var(--_swiper-navigation-bg, {value})',
            'navigationBgHover' => '--swiper-navigation-bg-hover:var(--_swiper-navigation-bg-hover, {value})',
            'navigationPadding' => '--swiper-navigation-padding:var(--_swiper-navigation-padding, {value})',
            'navigationBorderColor' => '--swiper-navigation-border-color:var(--_swiper-navigation-border-color, {value})',
            'navigationBorderColorHover' => '--swiper-navigation-border-color-hover:var(--_swiper-navigation-border-color-hover, {value})',
            'navigationShadow' => '--swiper-navigation-shadow:var(--_swiper-navigation-shadow, {value})',
            'navigationShadowHover' => '--swiper-navigation-shadow-hover:var(--_swiper-navigation-shadow-hover, {value})',

            // Pagination
            'paginationColor' => '--swiper-pagination-color:var(--_swiper-pagination-color, {value})',
            'paginationBulletSize' => '--swiper-pagination-bullet-size:var(--_swiper-pagination-bullet-size, {value})',
            'paginationBulletWidth' => '--swiper-pagination-bullet-width:var(--_swiper-pagination-bullet-width, {value})',
            'paginationBulletHeight' => '--swiper-pagination-bullet-height:var(--_swiper-pagination-bullet-height, {value})',
            'paginationBulletInactiveColor' => '--swiper-pagination-bullet-inactive-color:var(--_swiper-pagination-bullet-inactive-color, {value})',
            'paginationBulletInactiveOpacity' => '--swiper-pagination-bullet-inactive-opacity:var(--_swiper-pagination-bullet-inactive-opacity, {value})',
            'paginationBulletOpacity' => '--swiper-pagination-bullet-opacity:var(--_swiper-pagination-bullet-opacity, {value})',
            'paginationBulletHorizontalGap' => '--swiper-pagination-bullet-horizontal-gap:var(--_swiper-pagination-bullet-horizontal-gap, {value})',
            'paginationBulletVerticalGap' => '--swiper-pagination-bullet-vertical-gap:var(--_swiper-pagination-bullet-vertical-gap, {value})',
            'paginationFractionColor' => '--swiper-pagination-fraction-color:var(--_swiper-pagination-fraction-color, {value})',
            'paginationProgressbarBgColor' => '--swiper-pagination-progressbar-bg-color:var(--_swiper-pagination-progressbar-bg-color, {value})',
            'paginationProgressbarSize' => '--swiper-pagination-progressbar-size:var(--_swiper-pagination-progressbar-size, {value})',
            'paginationLeft' => '--swiper-pagination-left:var(--_swiper-pagination-left, {value})',
            'paginationRight' => '--swiper-pagination-right:var(--_swiper-pagination-right, {value})',
            'paginationTop' => '--swiper-pagination-top:var(--_swiper-pagination-top, {value})',
            'paginationBottom' => '--swiper-pagination-bottom:var(--_swiper-pagination-bottom, {value})',

            // Scrollbar
            'scrollbarBorderRadius' => '--swiper-scrollbar-border-radius:var(--_swiper-scrollbar-border-radius, {value})',
            'scrollbarTop' => '--swiper-scrollbar-top:var(--_swiper-scrollbar-top, {value})',
            'scrollbarBottom' => '--swiper-scrollbar-bottom:var(--_swiper-scrollbar-bottom, {value})',
            'scrollbarLeft' => '--swiper-scrollbar-left:var(--_swiper-scrollbar-left, {value})',
            'scrollbarRight' => '--swiper-scrollbar-right:var(--_swiper-scrollbar-right, {value})',
            'scrollbarSidesOffset' => '--swiper-scrollbar-sides-offset:var(--_swiper-scrollbar-sides-offset, {value})',
            'scrollbarBgColor' => '--swiper-scrollbar-bg-color:var(--_swiper-scrollbar-bg-color, {value})',
            'scrollbarDragBgColor' => '--swiper-scrollbar-drag-bg-color:var(--_swiper-scrollbar-drag-bg-color, {value})',
            'scrollbarSize' => '--swiper-scrollbar-size:var(--_swiper-scrollbar-size, {value})',

            // Thumbs
            'thumbActiveColor' => '--swiper-thumb-active-color:var(--_swiper-thumb-active-color, {value})',

            // Slide
            'slideBgColor' => '--swiper-slide-bg-color:var(--_swiper-slide-bg-color, {value})',
        ];

        foreach ($varMap as $key => $template) {
            if (isset($cssVars[$key]) && $cssVars[$key] !== '' && $cssVars[$key] !== null) {
                $styles[] = str_replace('{value}', $cssVars[$key], $template);
            }
        }

        return !empty($styles) ? implode(';', $styles) : '';
    }

    /**
     * Initialize Swiper for a specific slider
     * Outputs the initialization JavaScript code
     *
     * @param string $sliderId The unique slider ID
     * @param array $overrides Optional config overrides or additional options (callbacks, events, etc.)
     * @param bool $debug Whether to log initialization info to console
     * @return \Twig\Markup Empty markup (JS is registered with Craft's view)
     */
    public function initSlider(string $sliderId, array $overrides = [], bool $debug = false): \Twig\Markup
    {
        // Encode overrides as JSON for JavaScript
        $overridesJson = !empty($overrides) ? json_encode($overrides, JSON_UNESCAPED_SLASHES) : 'null';
        $debugFlag = $debug ? 'true' : 'false';

        // Sanitize slider ID for use as JavaScript function name (replace dashes with underscores)
        $safeFunctionName = str_replace('-', '_', $sliderId);

        $js = <<<JS
(function() {
    function initSwiper_{$safeFunctionName}() {
        const sliderId = '{$sliderId}';
        const swiperEl = document.getElementById(sliderId);
        const debug = {$debugFlag};

        if (swiperEl) {
            // Get config from data attribute
            let config = JSON.parse(swiperEl.getAttribute('data-swiper-config'));

            // Merge overrides if provided
            const overrides = {$overridesJson};
            if (overrides) {
                config = Object.assign({}, config, overrides);
            }

            if (debug) {
                console.log('Initializing Swiper "' + sliderId + '" with config:', config);
            }

            // Initialize Swiper
            const swiper = new Swiper('#' + sliderId, config);

            if (debug) {
                console.log('Swiper "' + sliderId + '" initialized:', swiper);
            }

            // Store instance on element for external access
            swiperEl.swiper = swiper;
        } else if (debug) {
            console.error('Swiper element not found: ' + sliderId);
        }
    }

    // Run immediately if DOM is ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSwiper_{$safeFunctionName});
    } else {
        initSwiper_{$safeFunctionName}();
    }
})();
JS;

        \Craft::$app->getView()->registerJs($js);

        return new \Twig\Markup('', 'UTF-8');
    }
}
