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
        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('slideshow-manager', 'Could not save settings.'));

            // Log validation failure
            $this->logWarning('Settings validation failed', ['errors' => $settings->getErrors()]);

            // Get the section to re-render the correct template with errors
            $section = $this->_validSettingsSection(
                $this->request->getBodyParam('section', 'general'),
            );
            $template = "slideshow-manager/settings/{$section}";

            return $this->renderTemplate($template, [
                'settings' => $settings,
            ]);
        }

        // Save settings to database
        if ($settings->saveToDatabase()) {
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
        $allowed = ['general', 'basic', 'layout', 'controls', 'advanced'];

        return in_array($section, $allowed, true) ? $section : 'general';
    }
}
