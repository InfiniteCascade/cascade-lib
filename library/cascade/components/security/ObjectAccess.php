<?php
namespace cascade\components\security;

use Yii;
use infinite\db\behaviors\ActiveAccess;
use infinite\security\Access;


class ObjectAccess extends \infinite\security\ObjectAccess {
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
			'primaryAccount' => Yii::$app->gk->primaryAccount
		]);
	}
}
?>