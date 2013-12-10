<?php
namespace cascade\commands;
use yii\db\Query;

class ToolsController extends \yii\console\Controller {
	public function actionIndex() {
		$m = \cascade\models\Relation::find(4);
		$m->active = 'bbbbbbbbb';
		\d($m->save());
		\d($m->start);

	}
}
?>