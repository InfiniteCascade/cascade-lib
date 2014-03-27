<?php
namespace cascade\components\security;

use Yii;
use infinite\db\behaviors\ActiveAccess;
use infinite\security\Access;


class ObjectAccess extends \infinite\security\ObjectAccess {
	public $specialAuthorities = ['Group'];

	public function determineVisibility()
	{
		$groupClass = Yii::$app->classes['Group'];
		$groupPrefix = $groupClass::modelPrefix();
		$publicGroup = Yii::$app->gk->publicGroup;
		$primaryAccount = Yii::$app->gk->primaryAccount;
		$actions = Yii::$app->gk->actionsByName;
		$readAction = $actions['read'];
		$publicAro = isset($this->requestors[$publicGroup->primaryKey]) ? $this->requestors[$publicGroup->primaryKey] : false;
		$primaryAccountAro = isset($this->requestors[$primaryAccount->primaryKey]) ? $this->requestors[$primaryAccount->primaryKey] : false;

		if ($publicAro && $publicAro[$readAction->primaryKey]->can($publicAro)) {
			return 'public';
		}

		if ($primaryAccountAro && $primaryAccountAro[$readAction->primaryKey]->can($primaryAccount)) {
			return 'internal';
		}

		foreach ($this->requestors as $aro => $access) {
			if (!$access[$readAction->primaryKey]->can($aro)) { continue; }
			if (preg_match('/^'. $groupPrefix .'\-/', $aro) === 0) {
				return 'shared';
			}
		}

		return 'private';
	}

	public function getRoleHelpText($roleItem)
	{
		return $this->object->objectType->getRoleHelpText($roleItem, $this->object);
	}

	public function getSpecialRequestors()
	{
		return array_merge(parent::getSpecialRequestors(), [
			'primaryAccount' => [
				'object' =>	Yii::$app->gk->primaryAccount,
				'maxRoleLevel' => Yii::$app->params['maxRoleLevels']['primaryAccount']
			]
		]);
	}

	protected function validateRole($role, $validationSettings)
	{
		$results = parent::validateRole($role, $validationSettings);
		$objectType = $validationSettings['object']->objectType;
		if (!in_array($objectType->systemId, $this->specialAuthorities) && $objectType->getBehavior('Authority') === null) {
			$results['errors'][] = $validationSettings['object']->descriptor . ' can not be shared with.';
		}
		return $results;
	}

	protected function fillValidationSettings($validationSettings)
	{
		if (isset($validationSettings['object'])) {
			$objectType = $validationSettings['object']->objectType;
			$objectTypeSettings = $objectType->getRoleValidationSettings($validationSettings['object']);
			foreach ($objectTypeSettings as $key => $value) {
				switch ($key) {
					case 'maxRoleLevel':
						if (isset($validationSettings[$key]) && $validationSettings[$key] !== true) {
							$validationSettings[$key] = min($validationSettings[$key], $value);
						} else {
							$validationSettings[$key] = $value;
						}
					break;
					case 'possibleRoles':
						if (isset($validationSettings[$key]) && $validationSettings[$key] !== true) {
							$validationSettings[$key] = array_intersect($validationSettings[$key], $value);
						} else {
							$validationSettings[$key] = $value;
						}
					break;
					default:
						$validationSettings[$key] = $value;
					break;
				}
			}
		}
		return $validationSettings;
	}
}
?>