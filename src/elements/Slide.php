<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\elements;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\actions\Restore;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use lindemannrock\slideshowmanager\elements\db\SlideQuery;
use lindemannrock\slideshowmanager\records\SlideRecord;
use lindemannrock\slideshowmanager\SlideshowManager;

/**
 * Slide element
 *
 * Represents a single slide in a slideshow with customizable fields
 */
class Slide extends Element
{
    /**
     * @var int|null Field ID this slide belongs to
     */
    public ?int $fieldId = null;

    /**
     * @var int|null Owner ID (entry/element containing the slideshow field)
     */
    public ?int $ownerId = null;

    /**
     * @var int Sort order
     */
    public int $sortOrder = 0;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('slideshow-manager', 'Slide');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('slideshow-manager', 'Slides');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'slide';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function find(): SlideQuery
    {
        return new SlideQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('slideshow-manager', 'All Slides'),
                'defaultSort' => ['sortOrder', 'asc'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        return [
            Delete::class,
            Restore::class,
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'sortOrder' => ['label' => Craft::t('slideshow-manager', 'Order')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'title',
            'sortOrder',
            'dateUpdated',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        // Get field layout from project config
        $fieldLayouts = Craft::$app->getProjectConfig()->get('slideshow-manager.fieldLayouts') ?? [];

        if (!empty($fieldLayouts)) {
            // Get the first (and only) field layout
            $fieldLayoutUid = array_key_first($fieldLayouts);
            $fieldLayout = Craft::$app->getFields()->getLayoutByUid($fieldLayoutUid);
            if ($fieldLayout) {
                return $fieldLayout;
            }
        }

        // Fallback to getting by type
        return Craft::$app->fields->getLayoutByType(Slide::class);
    }

    /**
     * @inheritdoc
     */
    public function canView(User $user): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canSave(User $user): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canDelete(User $user): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canDuplicate(User $user): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl("slideshow-manager/slides/{$this->id}");
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {
            $record = SlideRecord::findOne($this->id);

            if (!$record) {
                $record = new SlideRecord();
                $record->id = (int)$this->id;
            }

            $record->fieldId = $this->fieldId;
            $record->ownerId = $this->ownerId;
            $record->sortOrder = $this->sortOrder;

            $record->save(false);
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['fieldId', 'ownerId', 'sortOrder'], 'number', 'integerOnly' => true];
        $rules[] = [['title'], 'string', 'max' => 255];

        return $rules;
    }
}
