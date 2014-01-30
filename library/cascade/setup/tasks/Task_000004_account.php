<?php
namespace cascade\setup\tasks;

use cascade\models\Group;
use cascade\models\User;
use cascade\models\Relation;
use cascade\modules\core\TypeAccount\models\ObjectAccount;

class Task_000004_account extends \infinite\setup\Task {
	public function getTitle() {
		return 'Primary Account';
	}
	public function test() {
		return ObjectAccount::find()->disableAccessCheck()->count() > 0 && $this->setup->app()->params['primaryAccount'] !== '##primaryAccount##';
	}
	public function run() {
		$account = new ObjectAccount;
		$account->attributes = $this->input['account'];

		if ($account->save()) {
			$configResult = false;
			$paramsFilePath = INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'params.php';
			$moreError = 'File does not exist! ('. $paramsFilePath .')';
			if (file_exists($paramsFilePath)) {
				$currentContents = $originalContents = file_get_contents($paramsFilePath);
				$found = false;
				$currentContents = preg_split("/\\r\\n|\\r|\\n/", $currentContents);
				foreach($currentContents as $line => $content) {
					if (strpos($content, '$PRIMARY_ACCOUNT$') !== false) {
						$found = true;
						$currentContents[$line] = "\t'primaryAccount' => '{$account->id}', // \$PRIMARY_ACCOUNT\$ : COULD BREAK THINGS IF CHANGED";
					}
				}
				if ($found && file_put_contents($paramsFilePath, implode(PHP_EOL, $currentContents))) {
					$configResult = true;
				} else {
					$moreError = 'Invalid configuration template! <pre>'. implode(PHP_EOL, $currentContents) .'</pre>';
				}
			}
			if ($configResult) {
				return true;
			} else {
				$this->errors[] = "Could not save params file with primary account! ({$moreError})";
				return false;
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
		$fields['account'] = ['label' => 'Primary Account', 'fields' => []];
		$fields['account']['fields']['name'] = ['type' => 'text', 'label' => 'Name', 'required' => true, 'value' => function() { return 'Primary Account'; }];
		$fields['account']['fields']['aka'] = ['type' => 'text', 'label' => 'AKA', 'required' => false, 'value' => function() { return 'PA'; }];
		return $fields;
	}
}
?>