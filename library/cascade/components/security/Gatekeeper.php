<?php
namespace cascade\components\security;

use Yii;

use infinite\base\exceptions\Exception;
use cascade\modules\core\TypeAccount\models\ObjectAccount;

use cascade\components\db\ActiveRecord;
use infinite\db\behaviors\ActiveAccess;
use infinite\helpers\ArrayHelper;

class Gatekeeper extends \infinite\security\Gatekeeper {
	public function getPrimaryAccount() {
		return ObjectAccount::get(Yii::$app->params['primaryAccount']);
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

	public function getObjectVisibility($object)
	{
		$groupClass = $this->groupClass;
		$groupPrefix = $groupClass::modelPrefix();
		$objectAccess = $this->getObjectAccess($object);
		$publicGroup = $this->publicGroup;
		$actions = $this->actionsByName;
		$readAction = $actions['read'];
		$publicAro = isset($objectAccess['aros'][$publicGroup->primaryKey]) ? $objectAccess['aros'][$publicGroup->primaryKey] : false;;
		if ($publicAro && $publicAro[$readAction->primaryKey] === ActiveAccess::ACCESS_GRANTED) {
			return 'public';
		}
		foreach ($objectAccess['aros'] as $aro => $access) {
			if (preg_match('/^'. $groupPrefix .'\-/', $aro) === 0) {
				return 'shared';
			}
		}

		return 'private';
	}

	public function getObjectAccess($object)
	{
		$access = ['aros' => [], 'specialMap' => []];

		// get general aros
		$skipAros = [];
		$publicGroup = $this->publicGroup;
		$skipAros[] = $publicGroup->primaryKey;
		$access['specialMap']['public'] = $publicGroup->primaryKey;

		$aros = $this->getObjectAros($object);
		if (!in_array($publicGroup->primaryKey, $aros)) {
			$aros[] = $publicGroup->primaryKey;
		}

		foreach ($aros as $aro) {
			$access['aros'][$aro] = $this->getAccess($object, $aro, null, false);
		}
		return $access;
	}

	public function getObjectAros($object)
	{
		$aclClass = $this->aclClass;
		$typeModel = ActiveRecord::modelAlias(get_class($object));
		$where = [];
		$where = ['or', ['controlled_object_id' => $object->primaryKey], ['controlled_object_id' => null, 'object_model' => $typeModel]];
		$aros = $aclClass::find()->where($where)->groupBy(['accessing_object_id'])->select(['accessing_object_id'])->asArray()->all();
		$aros = ArrayHelper::getColumn($aros, 'accessing_object_id');
		return $aros;
	}

	protected function getTopAccess($baseAccess = [])
	{
		$aclClass = $this->aclClass;
		$base = $aclClass::find()->where(['accessing_object_id' => null, 'controlled_object_id' => null])->asArray()->all();
		return $this->fillActions($base, $baseAccess);
	}

	protected function getModelAccess($typeModel, $baseAccess = [])
	{
		$typeModel = ActiveRecord::modelAlias($typeModel);
		$aclClass = $this->aclClass;
		$base = $aclClass::find()->where(['accessing_object_id' => null, 'controlled_object_id' => null, 'object_model' => $typeModel])->asArray()->all();
		return $this->fillActions($base, $baseAccess);
	}


}
?>