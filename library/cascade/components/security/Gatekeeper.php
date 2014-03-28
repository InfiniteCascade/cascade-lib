<?php
namespace cascade\components\security;

use Yii;

use infinite\base\exceptions\Exception;
use cascade\modules\core\TypeAccount\models\ObjectAccount;

use cascade\components\db\ActiveRecord;
use infinite\db\behaviors\ActiveAccess;
use infinite\helpers\ArrayHelper;

class Gatekeeper extends \infinite\security\Gatekeeper {
	public $objectAccessClass = 'cascade\\components\\security\\ObjectAccess';

	public function getPrimaryAccount() {
		return ObjectAccount::get(Yii::$app->params['primaryAccount'], false);
	}

	public function setAuthority($authority)
	{
		if (!isset($authority['type']) 
			|| !($authorityTypeItem = Yii::$app->collectors['types']->getOne($authority['type']))
			|| !($authorityType = $authorityTypeItem->object))
		{
			throw new Exception("Access Control Authority is not set up correctly!" . print_r($authority, true));
		}
		unset($authority['type']);
		$authority['handler'] = $authorityType;
		return parent::setAuthority($authority);
	}

	public function getAuthority()
	{
		if (is_null($this->_authority)) {
			$this->authority = ['type' => 'User'];
		}
		return $this->_authority;
	}

	public function getControlledObject($object, $modelClass = null)
	{
		$objects = [];
		if (is_null($modelClass) && isset($object) && is_object($object)) {
			$modelClass = get_class($object);
		}
		$parent = parent::getControlledObject($object, $modelClass);
		if ($parent) {
			$objects[] = $parent->primaryKey;
		}
		if (!empty($modelClass)) {
			$dummyModel = new $modelClass;
			if (isset($dummyModel->objectType) && ($objectType = $dummyModel->objectType) && $objectType && isset($objectType->objectTypeModel)) {
				$objects[] = $objectType->objectTypeModel->primaryKey;
			}
		}
		if (empty($objects)) {
			return false;
		}
		return $objects;
	}
}
?>