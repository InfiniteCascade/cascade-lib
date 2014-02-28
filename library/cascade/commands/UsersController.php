<?php
namespace cascade\commands;
use Yii;
use yii\db\Query;
use yii\helpers\Console;
use cascade\models\User;
use cascade\models\Group;
use cascade\models\Relation;

class UsersController extends \infinite\console\Controller {
	public function actionIndex() {
		echo "Boom";
	}

	public function actionCreate() {

		$groups = Group::find()->disableAccessCheck()->all();
		$this->out("Groups");
		$options = [];
		$i = 1;
		$defaultGroup = null;
		foreach ($groups as $group) {
			if ($group->system === 'users') {
				$defaultGroup = $group->primaryKey;
			}
			$options[$i] = $group->primaryKey;
			$this->out("$i) {$group->descriptor}");
			$i++;
		}
		$group = Console::select("Choose", $options);
		if (empty($group)) {
			$group = $defaultGroup;
		} else {
			$group = $options[$group];
		}

		$user =  new User;
		$user->registerRelationModel(new Relation(['child_object_id' => $group]));
		$user->scenario = 'creation';
		$user->first_name = $this->prompt("First name");
		$user->last_name = $this->prompt("Last name");
		$user->email = $this->prompt("Email");
		$user->status = 1;
		$user->username = $this->prompt("Username");
		$user->password = $this->prompt("Password");
		if (!$user->validate()) {
			\d($user->errors);
			$this->stderr("User didn't validate!");
			exit;
		}
		$individual = $user->guessIndividual();
		if (empty($individual)) {
			if (!Console::confirm("No matching individual was found. Continue?")) {
				$this->stderr("Bye!");
				exit;
			}
		} elseif (is_object($individual)) {
			$user->object_individual_id = $individual->primaryKey;
			if (!Console::confirm("Matching individual was found ({$individual->descriptor})! Continue?")) {
				$this->stderr("Bye!");
				exit;
			}
		} else {
			$options = [];
			$i = 1;
			$this->out("Possible Individual Matches...");
			foreach ($individual as $ind) {
				$options[$i] = $ind->primaryKey;
				$this->out("$i) {$ind->descriptor}");
				$i++;
			}
			$user->object_individual_id = Console::select("Choose", $options);
		}

		if ($user->save()) {
			$this->out("User created!");
		} else {
			\d($user->errors);
			$this->out("Error creating user!");
		}
	}
}
?>