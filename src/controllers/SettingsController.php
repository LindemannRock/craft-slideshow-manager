<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\controllers;

use Craft;
use craft\web\Controller;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\slideshowmanager\models\Settings;
use lindemannrock\slideshowmanager\SlideshowManager;
use yii\web\Response;

/**
 * Settings Controller
 *
 * @since 1.0.0
 */
class SettingsController extends Controller
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
     * Settings index - redirect to general
     */
    public function actionIndex(): Response
    {
        return $this->redirect('slideshow-manager/settings/general');
    }

    /**
     * General settings tab
     */
    public function actionGeneral(): Response
    {
        $plugin = SlideshowManager::getInstance();
        $plugin->reloadSettings();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('slideshow-manager/settings/general', [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    /**
     * Basic settings tab
     */
    public function actionBasic(): Response
    {
        $plugin = SlideshowManager::getInstance();
        $plugin->reloadSettings();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('slideshow-manager/settings/basic', [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    /**
     * Layout & Responsive settings tab
     */
    public function actionLayout(): Response
    {
        $plugin = SlideshowManager::getInstance();
        $plugin->reloadSettings();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('slideshow-manager/settings/layout', [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    /**
     * Controls settings tab
     */
    public function actionControls(): Response
    {
        $plugin = SlideshowManager::getInstance();
        $plugin->reloadSettings();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('slideshow-manager/settings/controls', [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    /**
     * Styles settings tab
     */
    public function actionStyles(): Response
    {
        $plugin = SlideshowManager::getInstance();
        $plugin->reloadSettings();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('slideshow-manager/settings/styles', [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    /**
     * Advanced settings tab
     */
    public function actionAdvanced(): Response
    {
        $plugin = SlideshowManager::getInstance();
        $plugin->reloadSettings();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('slideshow-manager/settings/advanced', [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    /**
     * Save settings
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $plugin = SlideshowManager::getInstance();
        $section = $this->_validSettingsSection($this->request->getBodyParam('section', 'general'));
        $attributesToValidate = $this->_validationAttributesForSection($section);

        // Load current settings from database
        $settings = Settings::loadFromDatabase();

        // Get only the posted settings (fields from the current page)
        $settingsData = Craft::$app->getRequest()->getBodyParam('settings', []);

        // Log save attempt
        $this->logInfo('Settings save requested', [
            'userId' => Craft::$app->getUser()->getId(),
            'fields' => array_keys($settingsData),
        ]);

        // Handle default Swiper config - MERGE with existing, don't replace
        if (isset($settingsData['defaultSwiperConfig'])) {
            // Merge posted config with existing
            $settingsData['defaultSwiperConfig'] = array_replace_recursive(
                $settings->defaultSwiperConfig,
                $settingsData['defaultSwiperConfig']
            );
        }

        // Handle named Swiper CSS style presets stored inside swiperCssVars
        if (isset($settingsData['swiperCssVars']) && is_array($settingsData['swiperCssVars'])) {
            $rawStylesJson = $settingsData['swiperCssVars']['_stylesJson'] ?? null;
            if ($rawStylesJson !== null) {
                unset($settingsData['swiperCssVars']['_stylesJson']);

                $rawStylesJson = trim((string)$rawStylesJson);
                if ($rawStylesJson === '') {
                    $settingsData['swiperCssVars']['_styles'] = [];
                } else {
                    $decoded = json_decode($rawStylesJson, true);

                    if (!is_array($decoded)) {
                        $settings->addError('swiperCssVars._styles', Craft::t('slideshow-manager', 'Style presets JSON is invalid.'));
                        Craft::$app->getSession()->setError(Craft::t('slideshow-manager', 'Could not save settings.'));
                        $template = "slideshow-manager/settings/{$section}";
                        return $this->renderTemplate($template, [
                            'settings' => $settings,
                        ]);
                    }

                    // Ensure each preset is an object/array of CSS vars
                    foreach ($decoded as $handle => $vars) {
                        if (!is_string($handle) || $handle === '' || !is_array($vars)) {
                            $settings->addError('swiperCssVars._styles', Craft::t('slideshow-manager', 'Style presets must be an object keyed by style handle, with each value as an object.'));
                            Craft::$app->getSession()->setError(Craft::t('slideshow-manager', 'Could not save settings.'));
                            $template = "slideshow-manager/settings/{$section}";
                            return $this->renderTemplate($template, [
                                'settings' => $settings,
                            ]);
                        }
                    }

                    $settingsData['swiperCssVars']['_styles'] = $decoded;
                }
            }
        }

        // Skip validation for fields overridden by config.
        $attributesToValidate = array_values(array_filter(
            $attributesToValidate,
            static fn(string $attribute): bool => !$settings->isOverriddenByConfig($attribute),
        ));

        // Only update fields that were posted and are not overridden by config
        foreach ($settingsData as $key => $value) {
            if (!$settings->isOverriddenByConfig($key) && property_exists($settings, $key)) {
                // Check for setter method first (handles array conversions, etc.)
                $setterMethod = 'set' . ucfirst($key);
                if (method_exists($settings, $setterMethod)) {
                    $settings->$setterMethod($value);
                } else {
                    $settings->$key = $value;
                }
            }
        }

        // Validate
        if (!$settings->validate($attributesToValidate)) {
            Craft::$app->getSession()->setError(Craft::t('slideshow-manager', 'Could not save settings.'));

            // Log validation failure
            $this->logWarning('Settings validation failed', ['errors' => $settings->getErrors()]);

            $template = "slideshow-manager/settings/{$section}";

            return $this->renderTemplate($template, [
                'settings' => $settings,
            ]);
        }

        // Save settings to database
        if ($settings->saveToDatabase($attributesToValidate)) {
            // Log successful save
            $this->logInfo('Settings saved successfully', [
                'userId' => Craft::$app->getUser()->getId(),
            ]);

            Craft::$app->getSession()->setNotice(Craft::t('slideshow-manager', 'Settings saved.'));
        } else {
            $this->logError('Database save failed');
            Craft::$app->getSession()->setError(Craft::t('slideshow-manager', 'Could not save settings'));
            return null;
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * Validate and sanitize the settings section parameter
     *
     * @param string $section The section from POST data
     * @return string A validated section name
     */
    private function _validSettingsSection(string $section): string
    {
        $allowed = ['general', 'basic', 'layout', 'controls', 'styles', 'advanced'];

        return in_array($section, $allowed, true) ? $section : 'general';
    }

    /**
     * Return top-level settings attributes validated for the active section.
     *
     * @param string $section
     * @return array
     */
    private function _validationAttributesForSection(string $section): array
    {
        return match ($section) {
            'general' => ['pluginName', 'autoLoadSwiperCss', 'autoLoadSwiperJs', 'enableCache', 'cacheDuration', 'logLevel'],
            'basic', 'layout', 'controls', 'advanced' => ['defaultSwiperConfig'],
            'styles' => ['swiperCssVars'],
            default => [],
        };
    }
}
