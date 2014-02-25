<?php
namespace cascade\commands;
use yii\db\Query;

class ToolsController extends \infinite\console\Controller {
	public function actionIndex() {
		$z = new \infinite\db\models\User;
		$a = $z::find();
		$b = clone $a;

		$c = new \infinite\db\ActiveQuery;
		$d = clone $c;


		$e = new \yii\db\Query;
		$f = clone $e;
		$this->out("all done");
	}
}
?>