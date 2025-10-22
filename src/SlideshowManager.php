<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * Comprehensive slideshow management field with Swiper.js integration
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager;

use Craft;
use craft\base\Plugin;
use craft\base\Model;
use craft\base\Element;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use lindemannrock\slideshowmanager\fields\SlideshowField;
use lindemannrock\slideshowmanager\fields\SlideshowConfigField;
use lindemannrock\slideshowmanager\models\Settings;
use lindemannrock\slideshowmanager\variables\SlideshowVariable;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\logginglibrary\LoggingLibrary;
use yii\base\Event;

/**
 * Slideshow Manager Plugin
 *
 * @author    LindemannRock
 * @package   SlideshowManager
 * @since     1.0.0
 *
 * @property-read Settings $settings
 * @method Settings getSettings()
 */
class SlideshowManager extends Plugin
{
    use LoggingTrait;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Configure logging for this plugin
        $settings = $this->getSettings();
        LoggingLibrary::configure([
            'pluginHandle' => $this->handle,
            'pluginName' => $settings->pluginName ?? $this->name,
            'logLevel' => $settings->logLevel ?? 'error',
            'itemsPerPage' => $settings->itemsPerPage ?? 50,
            'permissions' => ['slideshowManager:viewLogs'],
        ]);

        // Override plugin name from config if available, otherwise use from database settings
        $configFileSettings = Craft::$app->getConfig()->getConfigFromFile('slideshow-manager');
        if (isset($configFileSettings['pluginName'])) {
            $this->name = $configFileSettings['pluginName'];
        } else {
            // Get from database settings
            if ($settings && !empty($settings->pluginName)) {
                $this->name = $settings->pluginName;
            }
        }

        $this->_registerCpRoutes();
        $this->_registerFieldTypes();
        $this->_registerVariables();
        $this->_registerTemplateRoots();
        $this->_registerPermissions();
        $this->_registerAssetAutoLoading();
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        // Load settings from database using the new method
        try {
            return Settings::loadFromDatabase();
        } catch (\Exception $e) {
            // Database might not be ready during installation
            Craft::info('Could not load settings from database: ' . $e->getMessage(), __METHOD__);
            return new Settings();
        }
    }

    /**
     * Force reload settings from database
     * This is needed because Craft caches settings in a private property
     */
    public function reloadSettings(): void
    {
        // Use reflection to access and clear the private _settings property
        $reflection = new \ReflectionClass(\craft\base\Plugin::class);
        $property = $reflection->getProperty('_settings');
        $property->setAccessible(true);
        $property->setValue($this, null);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect('slideshow-manager/settings');
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'slideshow-manager/settings',
            [
                'settings' => $this->getSettings(),
                'plugin' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();

        if ($item) {
            $item['icon'] = '@appicons/photo.svg';

            $item['subnav'] = [
                'settings' => [
                    'label' => Craft::t('slideshow-manager', 'Settings'),
                    'url' => 'slideshow-manager/settings',
                ],
            ];

            // Add logs section using logging library (only if installed and enabled)
            if (Craft::$app->getPlugins()->isPluginInstalled('logging-library') &&
                Craft::$app->getPlugins()->isPluginEnabled('logging-library')) {
                $item = LoggingLibrary::addLogsNav($item, $this->handle, [
                    'slideshowManager:viewLogs'
                ]);
            }
        }

        return $item;
    }

    /**
     * Register CP routes
     */
    private function _registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'slideshow-manager' => 'slideshow-manager/settings/index',
                    'slideshow-manager/settings' => 'slideshow-manager/settings/index',
                    'slideshow-manager/settings/general' => 'slideshow-manager/settings/general',
                    'slideshow-manager/settings/basic' => 'slideshow-manager/settings/basic',
                    'slideshow-manager/settings/layout' => 'slideshow-manager/settings/layout',
                    'slideshow-manager/settings/controls' => 'slideshow-manager/settings/controls',
                    'slideshow-manager/settings/advanced' => 'slideshow-manager/settings/advanced',
                    'slideshow-manager/settings/save' => 'slideshow-manager/settings/save',

                    // Logs routes - use logging-library controller
                    'slideshow-manager/logs' => 'logging-library/logs/index',
                    'slideshow-manager/logs/download' => 'logging-library/logs/download',
                ]);
            }
        );
    }

    /**
     * Register field types
     */
    private function _registerFieldTypes(): void
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SlideshowField::class;
                $event->types[] = SlideshowConfigField::class;
            }
        );
    }

    /**
     * Register template variables
     */
    private function _registerVariables(): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;
                $variable->set('slideshowManager', SlideshowVariable::class);
            }
        );
    }

    /**
     * Register template roots
     */
    private function _registerTemplateRoots(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['slideshow-manager'] = __DIR__ . '/templates';
            }
        );
    }

    /**
     * Register permissions
     */
    private function _registerPermissions(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => 'Slideshow Manager',
                    'permissions' => [
                        'slideshowManager:viewLogs' => [
                            'label' => 'View logs',
                        ],
                    ],
                ];
            }
        );
    }

    /**
     * Register auto-loading of Swiper CSS/JS assets
     * Only injects on site templates, not CP
     */
    private function _registerAssetAutoLoading(): void
    {
        Event::on(
            View::class,
            View::EVENT_END_BODY,
            function(Event $event) {
                /** @var View $view */
                $view = $event->sender;

                // Only run on site templates, not CP
                if (Craft::$app->getRequest()->getIsCpRequest()) {
                    return;
                }

                $settings = $this->getSettings();

                // Auto-load Swiper CSS in <head>
                if ($settings->autoLoadSwiperCss) {
                    $view->registerCssFile('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
                }

                // Auto-load Swiper JS before </body>
                if ($settings->autoLoadSwiperJs) {
                    $view->registerJsFile('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js');
                }
            }
        );
    }

}