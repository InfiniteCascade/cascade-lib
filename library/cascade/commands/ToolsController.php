<?php
namespace cascade\commands;
use yii\db\Query;

class ToolsController extends \infinite\console\Controller {
	public function actionIndex() {
		$v = false;
		\d(!$v);
	}
}
?>