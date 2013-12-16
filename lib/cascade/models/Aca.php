<?php

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

class Aca extends \infinite\db\models\Aca
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
