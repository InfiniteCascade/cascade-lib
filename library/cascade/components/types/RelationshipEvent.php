<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use yii\base\Event as BaseEvent;

/**
 * RelationshipEvent [[@doctodo class_description:cascade\components\types\RelationshipEvent]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationshipEvent extends BaseEvent
{
    /**
     * @var [[@doctodo var_type:parentEvent]] [[@doctodo var_description:parentEvent]]
     */
    public $parentEvent;

    /**
     * @var [[@doctodo var_type:parentObject]] [[@doctodo var_description:parentObject]]
     */
    public $parentObject;
    /**
     * @var [[@doctodo var_type:childObject]] [[@doctodo var_description:childObject]]
     */
    public $childObject;
    /**
     * @var [[@doctodo var_type:relationship]] [[@doctodo var_description:relationship]]
     */
    public $relationship;
}
