<?php
namespace cascade\setup\tasks;

use \infinite\setup\Exception;

class Task_000004_acl extends \infinite\setup\Task {
	public function getTitle() {
		return 'ACL';
	}
	public function getBaseRules() {
		return array(
			array(
				'action' => null,
				'controlled' => null,
				'accessing' => array('model' => 'cascade\models\Group', 'fields' => array('system' => 'top')),
				'object_model' => null,
				'task' => 'deny',
			),
			array(
				'action' => null,
				'controlled' => null,
				'accessing' => array('model' => 'cascade\models\Group', 'fields' => array('system' => 'staff')),
				'object_model' => null,
				'task' => 'deny',
			),
			array(
				'action' => null,
				'controlled' => null,
				'accessing' => array('model' => 'cascade\models\Group', 'fields' => array('system' => 'clients')),
				'object_model' => null,
				'task' => 'deny',
			),
			array(
				'action' => null,
				'controlled' => null,
				'accessing' => array('model' => 'cascade\models\Group', 'fields' => array('system' => 'guests')),
				'object_model' => null,
				'task' => 'deny',
			),
			array(
				'action' => null,
				'controlled' => null,
				'accessing' => array('model' => 'cascade\models\Group', 'fields' => array('system' => 'administrators')),
				'object_model' => null,
				'task' => 'allow',
			),
		);
	}
	public function test() {
		foreach ($this->baseRules as $rule) {
			if ($rule['task'] === 'allow') {
				$expected = true;
			} elseif ($rule['task'] === 'deny') {
				$expected = false;
			} else {
				$expected = null;
			}
			$controlled = $rule['controlled'];
			$accessing = $rule['accessing'];

			if (is_array($controlled)) {
				$model = $controlled['model'];
				$controlled = $model::find()->where($controlled['fields'])->one();
				if (!$controlled) {
					return false;
				}
			}

			if (is_array($accessing)) {
				$model = $accessing['model'];
				$accessing = $model::find()->where($accessing['fields'])->one();
				if (!$accessing) {
					return false;
				}
			}

			$result = $this->setup->app()->gk->can($rule['action'], $controlled, $accessing);
			if ($result !== $expected) {
				return false;
			}
		}
		return true;
	}
	public function run() {
		foreach ($this->baseRules as $rule) {
			$controlled = $rule['controlled'];
			$accessing = $rule['accessing'];

			if (is_array($controlled)) {
				$model = $controlled['model'];
				$controlled = $model::find()->where($controlled['fields'])->one();
				if (!$controlled) {
					throw new Exception("Could not find controlled object: ". print_r($rule['controlled'], true));
					return false;
				}
			}

			if (is_array($accessing)) {
				$model = $accessing['model'];
				$accessing = $model::find()->where($accessing['fields'])->one();
				if (!$accessing) {
					throw new Exception("Could not find accessing object: ". print_r($rule['accessing'], true));
					return false;
				}
			}

			if(!$this->setup->app()->gk->{$rule['task']}($rule['action'], $controlled, $accessing)) {
					throw new Exception("Could not set up rule: ". print_r(['rule' => $rule], true));
				return false;
			}
		}
		return true;
	}
	public function getFields() {
		return false;
	}
}
?>