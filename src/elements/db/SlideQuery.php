<?php
/**
 * Slideshow Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\slideshowmanager\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * Slide Query
 */
class SlideQuery extends ElementQuery
{
    /**
     * @var int|null Field ID
     */
    public ?int $fieldId = null;

    /**
     * @var int|null Owner ID
     */
    public ?int $ownerId = null;

    /**
     * @var int|null Sort order
     */
    public ?int $sortOrder = null;

    /**
     * Sets the fieldId parameter
     */
    public function fieldId(?int $value): self
    {
        $this->fieldId = $value;
        return $this;
    }

    /**
     * Sets the ownerId parameter
     */
    public function ownerId(?int $value): self
    {
        $this->ownerId = $value;
        return $this;
    }

    /**
     * Sets the sortOrder parameter
     */
    public function sortOrder(?int $value): self
    {
        $this->sortOrder = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('slideshowmanager_slides');

        $this->query->select([
            'slideshowmanager_slides.fieldId',
            'slideshowmanager_slides.ownerId',
            'slideshowmanager_slides.sortOrder',
        ]);

        if ($this->fieldId !== null) {
            $this->subQuery->andWhere(Db::parseParam('slideshowmanager_slides.fieldId', $this->fieldId));
        }

        if ($this->ownerId !== null) {
            $this->subQuery->andWhere(Db::parseParam('slideshowmanager_slides.ownerId', $this->ownerId));
        }

        if ($this->sortOrder !== null) {
            $this->subQuery->andWhere(Db::parseParam('slideshowmanager_slides.sortOrder', $this->sortOrder));
        }

        return parent::beforePrepare();
    }
}
