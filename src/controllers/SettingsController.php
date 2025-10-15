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
use lindemannrock\slideshowmanager\elements\Slide;
use lindemannrock\slideshowmanager\SlideshowManager;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use yii\web\Response;

/**
 * Settings Controller
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

        $request = Craft::$app->getRequest();
        $plugin = SlideshowManager::getInstance();
        $settings = $plugin->getSettings();

        // Get settings from request (nested under 'settings' key)
        $postedSettings = $request->getBodyParam('settings', []);

        // Only update non-overridden settings
        if (!$settings->isOverridden('pluginName')) {
            $settings->pluginName = $postedSettings['pluginName'] ?? $settings->pluginName;
        }

        if (!$settings->isOverridden('autoLoadSwiperCss')) {
            $settings->autoLoadSwiperCss = isset($postedSettings['autoLoadSwiperCss']) ? (bool)$postedSettings['autoLoadSwiperCss'] : false;
        }

        if (!$settings->isOverridden('autoLoadSwiperJs')) {
            $settings->autoLoadSwiperJs = isset($postedSettings['autoLoadSwiperJs']) ? (bool)$postedSettings['autoLoadSwiperJs'] : false;
        }

        if (!$settings->isOverridden('enableCache')) {
            $settings->enableCache = isset($postedSettings['enableCache']) ? (bool)$postedSettings['enableCache'] : false;
        }

        if (!$settings->isOverridden('cacheDuration')) {
            $settings->cacheDuration = isset($postedSettings['cacheDuration']) ? (int)$postedSettings['cacheDuration'] : $settings->cacheDuration;
        }

        if (!$settings->isOverriddenByConfig('logLevel')) {
            $settings->logLevel = $postedSettings['logLevel'] ?? $settings->logLevel;
        }

        // Handle default Swiper config - MERGE with existing, don't replace
        if (isset($postedSettings['defaultSwiperConfig'])) {
            // Merge posted config with existing
            $settings->defaultSwiperConfig = array_replace_recursive(
                $settings->defaultSwiperConfig,
                $postedSettings['defaultSwiperConfig']
            );
        }

        // Validate
        if (!$settings->validate()) {
            $errors = $settings->getErrors();
            $errorMessage = Craft::t('slideshow-manager', 'Couldn\'t save plugin settings.');

            // Add validation errors to the message
            if (!empty($errors)) {
                $errorDetails = [];
                foreach ($errors as $attribute => $attributeErrors) {
                    $errorDetails[] = $attribute . ': ' . implode(', ', $attributeErrors);
                }
                $errorMessage .= ' ' . implode(' ', $errorDetails);
            }

            Craft::$app->getSession()->setError($errorMessage);

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings
            ]);

            return null;
        }

        // Save to database using the new method
        if (!$settings->saveToDatabase()) {
            Craft::$app->getSession()->setError(Craft::t('slideshow-manager', 'Couldn\'t save plugin settings.'));
            return null;
        }

        // Force reload settings from database to clear Craft's internal cache
        $plugin->reloadSettings();

        Craft::$app->getSession()->setNotice(Craft::t('slideshow-manager', 'Plugin settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
