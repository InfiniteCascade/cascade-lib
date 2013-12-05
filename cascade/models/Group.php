<?php

namespace cascade\models;

class Group extends \infinite\db\models\Group
{
	use \cascade\components\types\ActiveRecordTrait {
		behaviors as baseBehaviors;
	}
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), self::baseBehaviors(), []);
	}
}
