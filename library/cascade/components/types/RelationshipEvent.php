<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use yii\base\Event as BaseEvent;

/**
 * RelationshipEvent [@doctodo write class description for RelationshipEvent]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class RelationshipEvent extends BaseEvent
{
    /**
     * @var __var_parentEvent_type__ __var_parentEvent_description__
     */
    public $parentEvent;

    /**
     * @var __var_parentObject_type__ __var_parentObject_description__
     */
    public $parentObject;
    /**
     * @var __var_childObject_type__ __var_childObject_description__
     */
    public $childObject;
    /**
     * @var __var_relationship_type__ __var_relationship_description__
     */
    public $relationship;
}
