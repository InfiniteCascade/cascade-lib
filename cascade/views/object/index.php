<?php
use Yii;

use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;
use infinite\web\grid\Grid;

$this->title = 'Dashboard';
echo Html::beginTag('div', ['class' => 'row']);
Yii::beginProfile("Build Grid");
$widgets = Yii::$app->collectors['widgets']->getLocation('front');
ArrayHelper::multisort($widgets, ['displayPriority', 'name'], [SORT_ASC, SORT_ASC]);
$grid = new Grid;
$cells = [];
Yii::beginProfile("Collect Widgets");
foreach ($widgets as $item => $widget) {
	$cells[] = Yii::$app->collectors['widgets']->build($widget, array(), array());
}
Yii::endProfile("Collect Widgets");
$grid->cells = $cells;
Yii::endProfile("Build Grid");
Yii::beginProfile("Render Grid");
echo $grid->generate();
Yii::endProfile("Render Grid");
echo Html::endTag('div');
?>