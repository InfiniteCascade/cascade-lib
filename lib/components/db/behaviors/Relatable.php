<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors;

use cascade\components\types\Relationship;

/**
 * Relatable [[@doctodo class_description:cascade\components\db\behaviors\Relatable]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relatable extends \canis\db\behaviors\Relatable
{
    /**
     * @inheritdoc
     */
    public function getInheritedParentModels($childObject)
    {
        $p = [];
        if (isset($this->owner->objectTypeItem) && !empty($this->owner->objectTypeItem->parents)) {
            foreach ($this->owner->objectTypeItem->parents as $relationship) {
                $relationshipId = $relationship->parent->systemId . '.' . $childObject->objectType->systemId;
                if (!Relationship::getById($relationshipId)) {
                    continue;
                }
                if ($relationship->parentInherit) {
                    $p[] = $relationship->parent->primaryModel;
                }
            }
        }

        return $p;
    }

    /**
     * @inheritdoc
     */
    public function getInheritedChildModels($parentObject)
    {
        $p = [];
        if (isset($this->owner->objectTypeItem) && !empty($this->owner->objectTypeItem->children)) {
            foreach ($this->owner->objectTypeItem->children as $relationship) {
                $relationshipId =  $parentObject->objectType->systemId . '.' . $relationship->child->systemId;
                if (!Relationship::getById($relationshipId)) {
                    continue;
                }
                if ($relationship->parentInherit) {
                    $p[] = $relationship->child->primaryModel;
                }
            }
        }

        return $p;
    }
}
