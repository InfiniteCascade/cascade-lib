<?php

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;

class User extends \infinite\db\models\User
{
	use TypesActiveRecordTrait {
		TypesActiveRecordTrait::behaviors as typesBehaviors;
	}

	use BaseActiveRecordTrait {
		BaseActiveRecordTrait::behaviors as baseBehaviors;
	}
	
	public $descriptorField = ['first_name', 'middle_name', 'last_name'];
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), self::baseBehaviors(), self::typesBehaviors(), []);
	}
	
}
