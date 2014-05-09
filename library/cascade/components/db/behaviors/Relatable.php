<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;
use cascade\components\types\Relationship;

class Relatable extends \infinite\db\behaviors\Relatable
{
	public function getInheritedParentModels($childObject)
    {
    	$p = [];
    	if (isset($this->owner->objectTypeItem) && !empty($this->owner->objectTypeItem->parents)) {
    		foreach ($this->owner->objectTypeItem->parents as $relationship) {
    			$relationshipId = $relationship->parent->systemId .'.'. $childObject->objectType->systemId;
    			if (!Relationship::getById($relationshipId)) { continue; }
    			if ($relationship->parentInherit) {
    				$p[] = $relationship->parent->primaryModel;
    			}
    		}
    	}
        return $p;
    }


    public function getInheritedChildModels($parentObject)
    {
        $p = [];
    	if (isset($this->owner->objectTypeItem) && !empty($this->owner->objectTypeItem->children)) {
    		foreach ($this->owner->objectTypeItem->children as $relationship) {
    			$relationshipId =  $parentObject->objectType->systemId .'.'. $relationship->child->systemId;
    			if (!Relationship::getById($relationshipId)) { continue; }
    			if ($relationship->parentInherit) {
    				$p[] = $relationship->child->primaryModel;
    			}
    		}
    	}
        return $p;
    }
}