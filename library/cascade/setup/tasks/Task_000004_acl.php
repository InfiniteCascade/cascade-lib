<?php
namespace cascade\setup\tasks;

use infinite\setup\Exception;
use cascade\models\User;

class Task_000004_acl extends AclTask {
	public function getBaseRules() {
		return [
			[
				'action' => null,
				'controlled' => null,
				'accessing' => ['model' => 'cascade\\models\\Group', 'fields' => ['system' => 'administrators']],
				'object_model' => null,
				'task' => 'allow',
			],
		];
	}

	public function test() {
		$run = User::find()->disableAccessCheck()->andWhere(['and', ['username' => 'system']])->count() > 0;
		return $run && parent::test();
	}
	public function run() {
		if (!User::systemUser()) {
			return false;
		}
		return parent::run();
	}
}
?>