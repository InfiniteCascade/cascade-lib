<?php

namespace cascade\models;

class Role extends \infinite\db\models\Role
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
