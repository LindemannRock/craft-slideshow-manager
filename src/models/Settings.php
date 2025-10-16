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
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * Slideshow Manager Settings Model
 */
class Settings extends Model
{
    use LoggingTrait;

    /**
     * @var array Track which settings are overridden by config
     */
    private array $_overriddenSettings = [];

    /**
     * @var string|null The public-facing name of the plugin
     */
    public ?string $pluginName = 'Slideshow Manager';

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

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        // Get config file overrides
        $configFileSettings = Craft::$app->getConfig()->getConfigFromFile('slideshow-manager');

        // Merge config file settings with defaults
        if ($configFileSettings) {
            $config = array_merge($configFileSettings, $config);
        }

        parent::__construct($config);
    }

    /**
     * Load settings from database
     *
     * @param Settings|null $settings Optional existing settings instance
     * @return self
     */
    public static function loadFromDatabase(?Settings $settings = null): self
    {
        if ($settings === null) {
            $settings = new self();
        }

        // Load from database
        try {
            $row = (new Query())
                ->from('{{%slideshowmanager_settings}}')
                ->where(['id' => 1])
                ->one();
        } catch (\Exception $e) {
            $settings->logError('Failed to load settings from database', ['error' => $e->getMessage()]);
            return $settings;
        }

        if ($row) {
            // Remove system fields that aren't attributes
            unset($row['id'], $row['dateCreated'], $row['dateUpdated'], $row['uid']);

            // Convert numeric boolean values to actual booleans
            $booleanFields = ['autoLoadSwiperCss', 'autoLoadSwiperJs', 'enableCache'];
            foreach ($booleanFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = (bool) $row[$field];
                }
            }

            // Convert numeric values to integers
            $integerFields = ['cacheDuration'];
            foreach ($integerFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = (int) $row[$field];
                }
            }

            // Decode JSON fields
            if (isset($row['defaultSwiperConfig'])) {
                $row['defaultSwiperConfig'] = Json::decode($row['defaultSwiperConfig']);
            }
            if (isset($row['swiperCssVars'])) {
                $row['swiperCssVars'] = Json::decode($row['swiperCssVars']);
            }

            // Set attributes from database
            $settings->setAttributes($row, false);
        } else {
            $settings->logWarning('No settings found in database');
        }

        // Apply config file overrides
        $configFileSettings = Craft::$app->getConfig()->getConfigFromFile('slideshow-manager');
        if ($configFileSettings) {
            // Track which settings are overridden
            foreach ($configFileSettings as $setting => $value) {
                if (property_exists($settings, $setting)) {
                    $settings->_overriddenSettings[] = $setting;

                    // For defaultSwiperConfig and swiperCssVars, do a deep merge instead of replace
                    if (in_array($setting, ['defaultSwiperConfig', 'swiperCssVars']) && is_array($value) && is_array($settings->$setting)) {
                        $settings->$setting = array_replace_recursive($settings->$setting, $value);
                    } else {
                        $settings->$setting = $value;
                    }
                }
            }
        }

        // Validate settings
        if (!$settings->validate()) {
            $settings->logError('Settings validation failed', ['errors' => $settings->getErrors()]);
        }

        return $settings;
    }

    /**
     * Save settings to database
     *
     * @return bool
     */
    public function saveToDatabase(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $db = Craft::$app->getDb();

        // For defaultSwiperConfig and swiperCssVars, we need to strip out config file overrides before saving
        $configToSave = $this->defaultSwiperConfig;
        $cssVarsToSave = $this->swiperCssVars;

        // Load the config file to see what's overridden
        $configPath = Craft::$app->getPath()->getConfigPath() . '/slideshow-manager.php';
        if (file_exists($configPath)) {
            $configFileSettings = require $configPath;

            // If defaultSwiperConfig exists in config file, we need to remove those overridden values
            if (isset($configFileSettings['defaultSwiperConfig'])) {
                $configToSave = $this->removeOverriddenValues($configToSave, $configFileSettings['defaultSwiperConfig']);
            }

            // If swiperCssVars exists in config file, we need to remove those overridden values
            if (isset($configFileSettings['swiperCssVars'])) {
                $cssVarsToSave = $this->removeOverriddenValues($cssVarsToSave, $configFileSettings['swiperCssVars']);
            }
        }

        // Build the attributes to save
        $attributes = [
            'pluginName' => $this->pluginName,
            'autoLoadSwiperCss' => $this->autoLoadSwiperCss,
            'autoLoadSwiperJs' => $this->autoLoadSwiperJs,
            'defaultSwiperConfig' => Json::encode($configToSave),
            'swiperCssVars' => Json::encode($cssVarsToSave),
            'enableCache' => $this->enableCache,
            'cacheDuration' => $this->cacheDuration,
            'logLevel' => $this->logLevel,
            'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
        ];

        $this->logDebug('Saving settings to database', ['fields' => array_keys($attributes)]);

        // Update existing settings (we know there's always one row from migration)
        try {
            $result = $db->createCommand()
                ->update('{{%slideshowmanager_settings}}', $attributes, ['id' => 1])
                ->execute();

            return $result !== false;
        } catch (\Exception $e) {
            $this->logError('Settings save failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Remove config file overridden values from an array
     * This ensures we don't save config file values to the database
     *
     * @param array $data The data to clean
     * @param array $configOverrides The config file overrides
     * @return array
     */
    private function removeOverriddenValues(array $data, array $configOverrides): array
    {
        foreach ($configOverrides as $key => $value) {
            if (array_key_exists($key, $data)) {
                // If the key exists in config, remove it entirely from database save
                // Don't try to merge - config file value will be used on load
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * Check if a setting is overridden by config file
     *
     * @param string $setting
     * @return bool
     */
    public function isOverridden(string $setting): bool
    {
        return in_array($setting, $this->_overriddenSettings, true);
    }

    /**
     * Check if a setting is overridden by config file
     * Supports dot notation for nested settings like: defaultSwiperConfig.navigation
     *
     * @param string $attribute Attribute name or dot-notation path
     * @return bool
     */
    public function isOverriddenByConfig(string $attribute): bool
    {
        $configPath = Craft::$app->getPath()->getConfigPath() . '/slideshow-manager.php';

        if (!file_exists($configPath)) {
            return false;
        }

        // Load the raw config file
        $rawConfig = require $configPath;

        // Handle dot notation for nested config
        if (str_contains($attribute, '.')) {
            $parts = explode('.', $attribute);
            $current = $rawConfig;

            foreach ($parts as $part) {
                if (!is_array($current) || !array_key_exists($part, $current)) {
                    return false;
                }
                $current = $current[$part];
            }

            return true;
        }

        // Simple attribute check
        if (array_key_exists($attribute, $rawConfig)) {
            return true;
        }

        // Check environment-specific configs
        $env = Craft::$app->getConfig()->env;
        if ($env && is_array($rawConfig[$env] ?? null) && array_key_exists($attribute, $rawConfig[$env])) {
            return true;
        }

        // Check wildcard config
        if (is_array($rawConfig['*'] ?? null) && array_key_exists($attribute, $rawConfig['*'])) {
            return true;
        }

        return false;
    }

    /**
     * Get all overridden settings
     *
     * @return array
     */
    public function getOverriddenSettings(): array
    {
        return $this->_overriddenSettings;
    }
}
