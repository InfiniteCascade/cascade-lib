<?php
namespace cascade\components\types;

use infinite\db\behaviors\SearchTerm;

class ActiveRecord extends \cascade\components\db\ActiveRecord {
	use ActiveRecordTrait {
		behaviors as baseBehaviors;
	}
	use SearchTerm;
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), self::baseBehaviors(), []);
	}
}
?>