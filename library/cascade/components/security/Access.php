<?php
namespace cascade\components\security;

use Yii;
use infinite\db\behaviors\ActiveAccess;


class Access extends \infinite\security\Access {
	public function determineVisibility()
	{
		$groupClass = Yii::$app->classes['Group'];
		$groupPrefix = $groupClass::modelPrefix();
		$publicGroup = Yii::$app->gk->publicGroup;
		$primaryAccount = Yii::$app->gk->primaryAccount;
		$actions = Yii::$app->gk->actionsByName;
		$readAction = $actions['read'];
		$publicAro = isset($this->requestors[$publicGroup->primaryKey]) ? $this->requestors[$publicGroup->primaryKey] : false;

		if ($publicAro && $publicAro[$readAction->primaryKey] === ActiveAccess::ACCESS_GRANTED) {
			return 'public';
		}

		foreach ($this->requestors as $aro => $access) {
			if ($aro === $primaryAccount->primaryKey) {
				return 'internal';
			} elseif (preg_match('/^'. $groupPrefix .'\-/', $aro) === 0) {
				return 'shared';
			}
		}

		return 'private';
	}
}
?>