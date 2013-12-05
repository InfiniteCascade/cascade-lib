<?php

namespace cascade\models;

class Aca extends \infinite\db\models\Aca
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
