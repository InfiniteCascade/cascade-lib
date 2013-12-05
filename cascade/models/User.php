<?php

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

class User extends \infinite\db\models\User
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
