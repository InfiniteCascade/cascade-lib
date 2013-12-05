<?php

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

class Group extends \infinite\db\models\Group
{
	use ActiveRecordTrait {
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
