<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;
use cascade\components\types\Relationship;

/**
 * PrimaryRelation [@doctodo write class description for PrimaryRelation].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class PrimaryRelation extends \infinite\db\behaviors\PrimaryRelation
{
    /**
     * @var __var__relationship_type__ __var__relationship_description__
     */
    protected $_relationship;

    /**
     * @inheritdoc
     */
    public function handlePrimary($role)
    {
        if (!parent::handlePrimary($role)) {
            return false;
        }
        if (!isset(Yii::$app->collectors['types'])) {
            return false;
        }
        if (empty($this->relationship)) {
            return false;
        }

        return $this->relationship->handlePrimary !== false;
    }

    /**
     * Get relationship.
     *
     * @return __return_getRelationship_type__ __return_getRelationship_description__
     */
    public function getRelationship()
    {
        if (is_null($this->_relationship)) {
            $parentObject = $this->owner->getParentObject(false);
            $childObject = $this->owner->getChildObject(false);
            if ($parentObject && $childObject) {
                $this->_relationship = Relationship::getOne($parentObject->objectTypeItem, $childObject->objectTypeItem);
            }
        }

        return $this->_relationship;
    }

    /**
     * Set relationship.
     *
     * @param cascade\components\types\Relationship $value __param_value_description__
     */
    public function setRelationship(Relationship $value)
    {
        $this->_relationship = $value;
    }
}
