<?php

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;
use infinite\db\behaviors\SearchTerm;

class Group extends \infinite\db\models\Group
{
	use TypesActiveRecordTrait {
		TypesActiveRecordTrait::behaviors as typesBehaviors;
	}

	use BaseActiveRecordTrait {
		BaseActiveRecordTrait::behaviors as baseBehaviors;
	}
	use SearchTerm;
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), self::baseBehaviors(), self::typesBehaviors(), []);
	}
}
