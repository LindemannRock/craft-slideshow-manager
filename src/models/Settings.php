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
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsDisplayNameTrait;
use lindemannrock\base\traits\SettingsPersistenceTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * Slideshow Manager Settings Model
 *
 * @since 1.0.0
 */
class Settings extends Model
{
    use LoggingTrait;
    use SettingsConfigTrait;
    use SettingsDisplayNameTrait;
    use SettingsPersistenceTrait;

    /**
     * @var string The name of the plugin as it appears in the Control Panel menu
     */
    public string $pluginName = 'Slideshow Manager';

    /**
     * @var bool Whether to load Swiper CSS automatically
     */
    public bool $autoLoadSwiperCss = true;

    /**
     * @var bool Whether to load Swiper JS automatically
     */
    public bool $autoLoadSwiperJs = true;

    /**
     * @var array Default Swiper configuration
     */
    public array $defaultSwiperConfig = [
        'loop' => true,
        'speed' => 300,
        'navigation' => true,
        'pagination' => [
            'enabled' => true,
            'clickable' => true,
            'type' => 'bullets',
            'customTemplate' => '{current} / {total}',
        ],
        'autoplay' => [
            'enabled' => false,
            'delay' => 3000,
            'disableOnInteraction' => false,
        ],
        'effect' => 'slide',
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
    ];

    /**
     * @var array Swiper CSS custom properties
     */
    public array $swiperCssVars = [];

    /**
     * @var bool Whether to enable caching
     */
    public bool $enableCache = true;

    /**
     * @var int Cache duration in seconds
     */
    public int $cacheDuration = 3600;

    /**
     * @var string The logging level for the plugin
     */
    public string $logLevel = 'error';

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
    protected function defineRules(): array
    {
        return [
            [['pluginName'], 'string'],
            [['autoLoadSwiperCss', 'autoLoadSwiperJs', 'enableCache'], 'boolean'],
            [['cacheDuration'], 'integer', 'min' => 1, 'max' => 2147483647],
            [['defaultSwiperConfig', 'swiperCssVars'], 'safe'],
            [['defaultSwiperConfig'], 'validateDefaultSwiperConfig'],
            [['swiperCssVars'], 'validateSwiperCssVars'],
            [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
            [['logLevel'], 'validateLogLevel'],
        ];
    }

    /**
     * Validate Swiper configuration values.
     */
    public function validateDefaultSwiperConfig(string $attribute): void
    {
        $config = $this->$attribute;
        if (!is_array($config)) {
            $this->addError($attribute, Craft::t(static::pluginHandle(), 'Swiper configuration must be an object.'));
            return;
        }

        $speed = $this->nestedValue($config, ['speed']);
        if ($speed !== null && (!$this->isNumeric($speed) || (float)$speed < 0 || (float)$speed > 100000)) {
            $this->addError('defaultSwiperConfig.speed', Craft::t(static::pluginHandle(), 'Speed must be between 0 and 100000 ms.'));
        }

        $slidesPerView = $this->nestedValue($config, ['slidesPerView']);
        if ($slidesPerView !== null && (!$this->isNumeric($slidesPerView) || (float)$slidesPerView < 1 || (float)$slidesPerView > 20)) {
            $this->addError('defaultSwiperConfig.slidesPerView', Craft::t(static::pluginHandle(), 'Slides per view must be between 1 and 20.'));
        }

        $spaceBetween = $this->nestedValue($config, ['spaceBetween']);
        if ($spaceBetween !== null && (!$this->isNumeric($spaceBetween) || (float)$spaceBetween < 0 || (float)$spaceBetween > 1000)) {
            $this->addError('defaultSwiperConfig.spaceBetween', Craft::t(static::pluginHandle(), 'Space between must be between 0 and 1000 pixels.'));
        }

        $autoplayDelay = $this->nestedValue($config, ['autoplay', 'delay']);
        if ($autoplayDelay !== null && (!$this->isNumeric($autoplayDelay) || (float)$autoplayDelay < 100 || (float)$autoplayDelay > 600000)) {
            $this->addError('defaultSwiperConfig.autoplay.delay', Craft::t(static::pluginHandle(), 'Autoplay delay must be between 100 and 600000 ms.'));
        }

        $customTemplate = $this->nestedValue($config, ['pagination', 'customTemplate']);
        if ($customTemplate !== null && is_string($customTemplate)) {
            if (preg_match_all('/\{([^}]+)\}/', $customTemplate, $matches) > 0) {
                foreach ($matches[1] as $token) {
                    if (!in_array($token, ['current', 'total'], true)) {
                        $this->addError(
                            'defaultSwiperConfig.pagination.customTemplate',
                            Craft::t(static::pluginHandle(), 'Unsupported token "{token}". Allowed tokens: {current}, {total}.', ['token' => $token])
                        );
                        break;
                    }
                }
            }
        }

        $loadPrevNext = $this->nestedValue($config, ['lazy', 'loadPrevNext']);
        if ($loadPrevNext !== null && (!$this->isNumeric($loadPrevNext) || (float)$loadPrevNext < 0 || (float)$loadPrevNext > 20)) {
            $this->addError('defaultSwiperConfig.lazy.loadPrevNext', Craft::t(static::pluginHandle(), 'Load prev/next must be between 0 and 20.'));
        }

        $zoomMin = $this->nestedValue($config, ['zoom', 'minRatio']);
        $zoomMax = $this->nestedValue($config, ['zoom', 'maxRatio']);
        if ($zoomMin !== null && (!$this->isNumeric($zoomMin) || (float)$zoomMin < 1 || (float)$zoomMin > 20)) {
            $this->addError('defaultSwiperConfig.zoom.minRatio', Craft::t(static::pluginHandle(), 'Zoom min ratio must be between 1 and 20.'));
        }
        if ($zoomMax !== null && (!$this->isNumeric($zoomMax) || (float)$zoomMax < 1 || (float)$zoomMax > 20)) {
            $this->addError('defaultSwiperConfig.zoom.maxRatio', Craft::t(static::pluginHandle(), 'Zoom max ratio must be between 1 and 20.'));
        }
        if ($this->isNumeric($zoomMin) && $this->isNumeric($zoomMax) && (float)$zoomMax < (float)$zoomMin) {
            $this->addError('defaultSwiperConfig.zoom.maxRatio', Craft::t(static::pluginHandle(), 'Zoom max ratio must be greater than or equal to zoom min ratio.'));
        }

        $breakpoints = $this->nestedValue($config, ['breakpoints']);
        if ($breakpoints !== null) {
            if (!is_array($breakpoints)) {
                $this->addError('defaultSwiperConfig.breakpoints', Craft::t(static::pluginHandle(), 'Breakpoints must be an array.'));
            } else {
                foreach ($breakpoints as $index => $breakpoint) {
                    if (!is_array($breakpoint)) {
                        $this->addError('defaultSwiperConfig.breakpoints', Craft::t(static::pluginHandle(), 'Breakpoint #{index} must be an object.', ['index' => (string)($index + 1)]));
                        continue;
                    }
                    $width = $breakpoint['width'] ?? null;
                    $bpSlidesPerView = $breakpoint['slidesPerView'] ?? null;
                    $bpSpaceBetween = $breakpoint['spaceBetween'] ?? null;
                    if (!$this->isNumeric($width) || (float)$width < 0) {
                        $this->addError('defaultSwiperConfig.breakpoints', Craft::t(static::pluginHandle(), 'Breakpoint width must be a number greater than or equal to 0.'));
                        break;
                    }
                    if (!$this->isNumeric($bpSlidesPerView) || (float)$bpSlidesPerView < 1 || (float)$bpSlidesPerView > 20) {
                        $this->addError('defaultSwiperConfig.breakpoints', Craft::t(static::pluginHandle(), 'Breakpoint slides per view must be between 1 and 20.'));
                        break;
                    }
                    if (!$this->isNumeric($bpSpaceBetween) || (float)$bpSpaceBetween < 0 || (float)$bpSpaceBetween > 1000) {
                        $this->addError('defaultSwiperConfig.breakpoints', Craft::t(static::pluginHandle(), 'Breakpoint space between must be between 0 and 1000 pixels.'));
                        break;
                    }
                }
            }
        }
    }

    /**
     * Validate CSS vars settings payload.
     */
    public function validateSwiperCssVars(string $attribute): void
    {
        $cssVars = $this->$attribute;
        if (!is_array($cssVars)) {
            $this->addError($attribute, Craft::t(static::pluginHandle(), 'CSS vars configuration must be an object.'));
            return;
        }

        $activeHandle = $cssVars['_active'] ?? '';
        if ($activeHandle !== '' && !preg_match('/^[a-z0-9_-]+$/i', (string)$activeHandle)) {
            $this->addError('swiperCssVars._active', Craft::t(static::pluginHandle(), 'Active style handle may only contain letters, numbers, hyphens, and underscores.'));
        }

        $styles = $cssVars['_styles'] ?? null;
        if ($styles !== null && !is_array($styles)) {
            $this->addError('swiperCssVars._styles', Craft::t(static::pluginHandle(), 'Style presets must be an object keyed by style handle.'));
        }

        $lengthFields = [
            'navigationSize',
            'navigationTopOffset',
            'navigationSidesOffset',
            'navigationPadding',
            'navigationRadius',
            'paginationBulletSize',
            'paginationBulletWidth',
            'paginationBulletHeight',
            'paginationBulletHorizontalGap',
            'paginationBulletVerticalGap',
            'paginationProgressbarSize',
            'paginationLeft',
            'paginationRight',
            'paginationTop',
            'paginationBottom',
            'scrollbarBorderRadius',
            'scrollbarTop',
            'scrollbarBottom',
            'scrollbarLeft',
            'scrollbarRight',
            'scrollbarSidesOffset',
            'scrollbarSize',
        ];
        $colorFields = [
            'themeColor',
            'navigationColor',
            'navigationInactiveColor',
            'navigationBg',
            'navigationBgHover',
            'navigationBorderColor',
            'navigationBorderColorHover',
            'paginationColor',
            'paginationBulletInactiveColor',
            'paginationFractionColor',
            'paginationProgressbarBgColor',
            'scrollbarBgColor',
            'scrollbarDragBgColor',
            'thumbActiveColor',
            'slideBgColor',
        ];
        $opacityFields = [
            'paginationBulletInactiveOpacity',
            'paginationBulletOpacity',
        ];

        foreach ($lengthFields as $field) {
            $value = trim((string)($cssVars[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            if (!$this->isValidCssLengthOrKeyword($value, ['auto', 'inherit'])) {
                $this->addError(
                    "swiperCssVars.{$field}",
                    Craft::t(
                        static::pluginHandle(),
                        'Invalid value. Use a CSS length/percentage (for example: 44px, 0.5rem, 50%), `auto`, `inherit`, or `var(...)`/`calc(...)`.'
                    )
                );
            }
        }

        foreach ($colorFields as $field) {
            $value = trim((string)($cssVars[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            if (!$this->isValidCssColor($value)) {
                $this->addError(
                    "swiperCssVars.{$field}",
                    Craft::t(
                        static::pluginHandle(),
                        'Invalid color. Use hex, rgb()/rgba(), hsl()/hsla(), `transparent`, `currentColor`, or `var(...)`.'
                    )
                );
            }
        }

        foreach ($opacityFields as $field) {
            $value = trim((string)($cssVars[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            if (!$this->isNumeric($value) || (float)$value < 0 || (float)$value > 1) {
                $this->addError(
                    "swiperCssVars.{$field}",
                    Craft::t(static::pluginHandle(), 'Invalid opacity. Use a number between 0 and 1.')
                );
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function isNumeric(mixed $value): bool
    {
        return is_int($value) || is_float($value) || (is_string($value) && is_numeric($value));
    }

    /**
     * @param array<string,mixed> $source
     * @param array<int,string> $path
     * @return mixed
     */
    private function nestedValue(array $source, array $path): mixed
    {
        $value = $source;
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private function isValidCssLengthOrKeyword(string $value, array $keywords = []): bool
    {
        if (in_array(strtolower($value), array_map('strtolower', $keywords), true)) {
            return true;
        }

        if (preg_match('/^(var|calc|clamp|min|max)\(.+\)$/i', $value) === 1) {
            return true;
        }

        return preg_match('/^-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|pt|pc|cm|mm|in)$/i', $value) === 1;
    }

    private function isValidCssColor(string $value): bool
    {
        if (preg_match('/^(transparent|currentColor|inherit)$/i', $value) === 1) {
            return true;
        }

        if (preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value) === 1) {
            return true;
        }

        if (preg_match('/^(rgba?|hsla?)\(.+\)$/i', $value) === 1) {
            return true;
        }

        return preg_match('/^var\(.+\)$/i', $value) === 1;
    }

    /**
     * Validates the log level - debug requires devMode
     */
    public function validateLogLevel($attribute, $params, $validator)
    {
        $logLevel = $this->$attribute;

        // Reset session warning when devMode is true - allows warning to show again if devMode changes
        // Only handle session in web requests, not console
        if (Craft::$app->getConfig()->getGeneral()->devMode && !Craft::$app->getRequest()->getIsConsoleRequest()) {
            Craft::$app->getSession()->remove('sm_debug_config_warning');
        }

        // Debug level is only allowed when devMode is enabled - auto-fallback to info
        if ($logLevel === 'debug' && !Craft::$app->getConfig()->getGeneral()->devMode) {
            $this->$attribute = 'info';

            // Only log warning once per session for config overrides
            if ($this->isOverriddenByConfig('logLevel')) {
                if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
                    // Web request - use session to prevent duplicate warnings
                    if (Craft::$app->getSession()->get('sm_debug_config_warning') === null) {
                        $this->logWarning('Log level "debug" from config file changed to "info" because devMode is disabled. Please update your config/slideshow-manager.php file.');
                        Craft::$app->getSession()->set('sm_debug_config_warning', true);
                    }
                } else {
                    // Console request - just log without session
                    $this->logWarning('Log level "debug" from config file changed to "info" because devMode is disabled. Please update your config/slideshow-manager.php file.');
                }
            } else {
                // Database setting - save the correction
                $this->logWarning('Log level automatically changed from "debug" to "info" because devMode is disabled. This setting has been saved.');
                $this->saveToDatabase();
            }
        }
    }

    // =========================================================================
    // Trait Configuration Methods
    // =========================================================================

    /**
     * Database table name for settings storage
     */
    protected static function tableName(): string
    {
        return 'slideshowmanager_settings';
    }

    /**
     * Plugin handle for config file resolution
     */
    protected static function pluginHandle(): string
    {
        return 'slideshow-manager';
    }

    /**
     * Fields that should be cast to boolean
     */
    protected static function booleanFields(): array
    {
        return [
            'autoLoadSwiperCss',
            'autoLoadSwiperJs',
            'enableCache',
        ];
    }

    /**
     * Fields that should be cast to integer
     */
    protected static function integerFields(): array
    {
        return [
            'cacheDuration',
        ];
    }

    /**
     * Fields that should be JSON encoded/decoded
     */
    protected static function jsonFields(): array
    {
        return [
            'defaultSwiperConfig',
            'swiperCssVars',
        ];
    }

    /**
     * Fields to exclude from database save
     */
    protected static function excludeFromSave(): array
    {
        return [];
    }
}
