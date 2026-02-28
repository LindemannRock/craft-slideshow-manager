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
     * @var string The public-facing name of the plugin
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
            [['cacheDuration'], 'integer', 'min' => 1],
            [['defaultSwiperConfig', 'swiperCssVars'], 'safe'],
            [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
            [['logLevel'], 'validateLogLevel'],
        ];
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
