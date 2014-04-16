<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use yii\base\Event as BaseEvent;

class RelationshipEvent extends BaseEvent
{
    public $parentEvent;

    public $parentObject;
    public $childObject;
    public $relationship;
}
