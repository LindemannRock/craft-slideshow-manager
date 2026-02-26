<?php
/**
 * Slideshow Manager config.php
 *
 * This file exists only as a template for the Slideshow Manager settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'slideshow-manager.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 *
 * @since 1.0.0
 */

return [
    // Global settings
    '*' => [
        // ========================================
        // GENERAL SETTINGS
        // ========================================
        // Basic plugin configuration

        'pluginName' => 'Slideshow Manager',

        // Logging Settings
        'logLevel' => 'error',             // Log level: 'debug', 'info', 'warning', 'error'


        // ========================================
        // FRONTEND ASSETS
        // ========================================
        // Swiper library loading

        'autoLoadSwiperCss' => true,       // Automatically load Swiper CSS on the frontend
        'autoLoadSwiperJs' => true,        // Automatically load Swiper JS on the frontend


        // ========================================
        // CACHE SETTINGS
        // ========================================
        // Performance and caching configuration

        'enableCache' => true,             // Enable caching for slideshow rendering
        'cacheDuration' => 3600,           // Cache duration in seconds (1 hour)


        // ========================================
        // SWIPER CONFIGURATION
        // ========================================
        // Default Swiper configuration applied to all slideshows
        // Individual slideshows can override these settings

        'defaultSwiperConfig' => [
            // Layout
            'slidesPerView' => 1,
            'spaceBetween' => 0,
            'centeredSlides' => false,

            // Navigation
            'navigation' => true,
            'navigationVisibility' => 'default', // default, hide-mobile, hide-desktop, mobile-only, desktop-only

            // Pagination
            'pagination' => [
                'enabled' => true,
                'clickable' => true,
                'type' => 'bullets',           // bullets, fraction, progressbar
            ],
            'paginationVisibility' => 'default', // default, hide-mobile, hide-desktop, mobile-only, desktop-only

            // Autoplay
            'autoplay' => [
                'enabled' => false,
                'delay' => 3000,
                'disableOnInteraction' => false,
            ],

            // Behavior
            'loop' => true,
            'speed' => 300,
            'effect' => 'slide',               // slide, fade, cube, coverflow, flip, cards, creative

            // Grid
            'grid' => [
                'enabled' => false,
                'rows' => 1,
                'fill' => 'row',               // row, column
            ],

            // Controls
            'keyboard' => [
                'enabled' => true,
                'onlyInViewport' => true,
            ],
            'mousewheel' => [
                'enabled' => false,
                'forceToAxis' => true,
            ],
            'scrollbar' => [
                'enabled' => false,
                'draggable' => true,
            ],
            'hashNavigation' => [
                'enabled' => false,
                'watchState' => false,
            ],

            // Advanced
            'freeMode' => [
                'enabled' => false,
                'sticky' => false,
            ],
            'lazy' => [
                'enabled' => false,
                'loadPrevNext' => 1,
            ],
            'parallax' => [
                'enabled' => false,
            ],
            'zoom' => [
                'enabled' => false,
                'maxRatio' => 3,
                'minRatio' => 1,
            ],
            'virtual' => [
                'enabled' => false,
            ],
            'a11y' => [
                'enabled' => true,
            ],

            // Responsive breakpoints
            'breakpoints' => [
                [
                    'width' => 0,
                    'slidesPerView' => 1,
                    'spaceBetween' => 0,
                ],
                [
                    'width' => 640,
                    'slidesPerView' => 1,
                    'spaceBetween' => 10,
                ],
                [
                    'width' => 768,
                    'slidesPerView' => 2,
                    'spaceBetween' => 20,
                ],
                [
                    'width' => 1024,
                    'slidesPerView' => 3,
                    'spaceBetween' => 30,
                ],
            ],
        ],


        // ========================================
        // SWIPER CSS CUSTOM PROPERTIES
        // ========================================
        // Customize Swiper styling via CSS variables
        // These allow customization via --_swiper-* variables with fallbacks
        // Set to null or empty array to use Swiper defaults

        'swiperCssVars' => [
            // Theme
            // 'themeColor' => 'var(--color-brand)',

            // Navigation
            // 'navigationSize' => '44px',
            // 'navigationTopOffset' => '50%',
            // 'navigationSidesOffset' => '10px',
            // 'navigationColor' => 'var(--color-brand)',
            // 'navigationInactiveColor' => 'rgba(0, 0, 0, 0.3)',
            // 'navigationBg' => 'transparent',
            // 'navigationBgHover' => 'rgba(0, 0, 0, 0.05)',
            // 'navigationPadding' => '0.5rem',
            // 'navigationRadius' => '9999px',
            // 'navigationBorderColor' => 'transparent',
            // 'navigationBorderColorHover' => 'transparent',
            // 'navigationShadow' => 'none',
            // 'navigationShadowHover' => 'none',

            // Pagination
            // 'paginationColor' => 'var(--color-brand)',
            // 'paginationBulletSize' => '8px',
            // 'paginationBulletWidth' => '8px',
            // 'paginationBulletHeight' => '8px',
            // 'paginationBulletInactiveColor' => '#000',
            // 'paginationBulletInactiveOpacity' => '0.2',
            // 'paginationBulletOpacity' => '1',
            // 'paginationBulletHorizontalGap' => '4px',
            // 'paginationBulletVerticalGap' => '6px',
            // 'paginationFractionColor' => 'inherit',
            // 'paginationProgressbarBgColor' => 'rgba(0, 0, 0, 0.25)',
            // 'paginationProgressbarSize' => '4px',
            // 'paginationLeft' => 'auto',
            // 'paginationRight' => '8px',
            // 'paginationTop' => 'auto',
            // 'paginationBottom' => '8px',

            // Scrollbar
            // 'scrollbarBorderRadius' => '10px',
            // 'scrollbarTop' => 'auto',
            // 'scrollbarBottom' => '4px',
            // 'scrollbarLeft' => 'auto',
            // 'scrollbarRight' => '4px',
            // 'scrollbarSidesOffset' => '1%',
            // 'scrollbarBgColor' => 'rgba(0, 0, 0, 0.1)',
            // 'scrollbarDragBgColor' => 'rgba(0, 0, 0, 0.5)',
            // 'scrollbarSize' => '4px',

            // Thumbs
            // 'thumbActiveColor' => 'var(--color-brand)',

            // Slide
            // 'slideBgColor' => 'transparent',
        ],
    ],

    // Dev environment settings
    'dev' => [
        'logLevel' => 'debug',             // More verbose logging in dev
        'enableCache' => false,            // No cache - see changes immediately
    ],

    // Staging environment settings
    'staging' => [
        'logLevel' => 'info',              // Moderate logging in staging
        'enableCache' => true,
        'cacheDuration' => 1800,           // 30 minutes - balance testing/performance
    ],

    // Production environment settings
    'production' => [
        'logLevel' => 'error',             // Only errors in production
        'enableCache' => true,
        'cacheDuration' => 3600,           // 1 hour - optimal performance
    ],
];
