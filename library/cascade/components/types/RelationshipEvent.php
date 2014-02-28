<?php
namespace cascade\components\types;

use yii\base\Event as BaseEvent;

class RelationshipEvent extends BaseEvent
{
	public $parentEvent;
	
	public $parentObject;
	public $childObject;
	public $relationship;
}
