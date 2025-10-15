<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTables();

        return true;
    }

    /**
     * Creates the tables.
     */
    protected function createTables(): void
    {
        // Slides table
        $this->createTable('{{%slideshowmanager_slides}}', [
            'id' => $this->primaryKey(),
            'fieldId' => $this->integer(),
            'ownerId' => $this->integer(),
            'sortOrder' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Add indexes for better query performance
        $this->createIndex(null, '{{%slideshowmanager_slides}}', ['fieldId']);
        $this->createIndex(null, '{{%slideshowmanager_slides}}', ['ownerId']);
        $this->createIndex(null, '{{%slideshowmanager_slides}}', ['sortOrder']);

        // Add foreign keys
        $this->addForeignKey(null, '{{%slideshowmanager_slides}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');

        // Configs table (stores per-entry Swiper configuration)
        $this->createTable('{{%slideshowmanager_configs}}', [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer()->notNull(),
            'fieldId' => $this->integer()->notNull(),
            'config' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Add indexes for better query performance
        $this->createIndex(null, '{{%slideshowmanager_configs}}', ['elementId', 'fieldId'], true);
        $this->createIndex(null, '{{%slideshowmanager_configs}}', ['elementId']);
        $this->createIndex(null, '{{%slideshowmanager_configs}}', ['fieldId']);

        // Add foreign keys
        $this->addForeignKey(null, '{{%slideshowmanager_configs}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%slideshowmanager_configs}}', ['fieldId'], '{{%fields}}', ['id'], 'CASCADE');

        // Settings table (single row)
        $this->createTable('{{%slideshowmanager_settings}}', [
            'id' => $this->primaryKey(),

            // Plugin settings
            'pluginName' => $this->string()->null(),

            // Asset loading settings
            'autoLoadSwiperCss' => $this->boolean()->notNull()->defaultValue(true),
            'autoLoadSwiperJs' => $this->boolean()->notNull()->defaultValue(true),

            // Default Swiper configuration (stored as JSON)
            'defaultSwiperConfig' => $this->text(),

            // Swiper CSS custom properties (stored as JSON)
            'swiperCssVars' => $this->text(),

            // Caching settings
            'enableCache' => $this->boolean()->notNull()->defaultValue(true),
            'cacheDuration' => $this->integer()->notNull()->defaultValue(3600),

            // Logging settings
            'logLevel' => $this->string(20)->notNull()->defaultValue('error'),

            // System fields
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Insert default settings row
        $this->insert('{{%slideshowmanager_settings}}', [
            'id' => 1,
            'pluginName' => 'Slideshow Manager',
            'autoLoadSwiperCss' => true,
            'autoLoadSwiperJs' => true,
            'defaultSwiperConfig' => Json::encode([
                'slidesPerView' => 1,
                'spaceBetween' => 0,
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
            ]),
            'swiperCssVars' => '{}',
            'enableCache' => true,
            'cacheDuration' => 3600,
            'logLevel' => 'error',
            'dateCreated' => Db::prepareDateForDb(new \DateTime()),
            'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
            'uid' => StringHelper::UUID(),
        ]);
    }

    /**
     * Drops the tables.
     */
    protected function dropTables(): void
    {
        $this->dropTableIfExists('{{%slideshowmanager_configs}}');
        $this->dropTableIfExists('{{%slideshowmanager_slides}}');
        $this->dropTableIfExists('{{%slideshowmanager_settings}}');
    }
}
