<?php
namespace cascade\setup\tasks;

use cascade\models\Group;
use cascade\models\User;
use cascade\models\Relation;

class Task_000006_admin_user extends \infinite\setup\Task {
	public function getTitle() {
		return 'Admin User';
	}
	public function test() {
		return User::find()->count() > 0;
	}
	public function run() {
		$user = new User;
		$user->scenario = 'creation';
		$user->attributes = $this->input['admin'];
		$user->status = User::STATUS_ACTIVE;
		$superGroup = Group::find()->where(['system' => 'super_administrators'])->one();
		if (!$superGroup) {
			throw new Exception("Unable to find super_administrators group!");
		}

		if ($user->save()) {
			$rel = new Relation;
			$rel->parent_object_id = $superGroup->primaryKey;
			$rel->child_object_id = $user->primaryKey;
			$rel->active = 1;
			if ($rel->save()) {
				return true;
			} else {
				$this->errors[] = "Could not assign user to the Super Administrators group.";
			}
		}
		foreach ($user->errors as $field => $errors) {
			$this->fieldErrors[$field] = implode('; ', $errors);
		}
		var_dump($this->fieldErrors);exit;
		return false;
	}
	public function getFields() {
		$fields = [];
		$fields['admin'] = ['label' => 'First Admin User', 'fields' => []];
		$fields['admin']['fields']['first_name'] = ['type' => 'text', 'label' => 'First Name', 'required' => true, 'value' => function() { return ''; }];
		$fields['admin']['fields']['last_name'] = ['type' => 'text', 'label' => 'Last Name', 'required' => true, 'value' => function() { return ''; }];
		$fields['admin']['fields']['username'] = ['type' => 'text', 'label' => 'Username', 'required' => true, 'value' => function() { return 'admin'; }];
		$fields['admin']['fields']['password'] = ['type' => 'text', 'label' => 'Password', 'required' => true, 'value' => function() { return 'admin'; }];
		return $fields;
	}
}
?>