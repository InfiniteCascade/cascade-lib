<?php

namespace cascade\models;

class User extends \infinite\db\models\User
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
