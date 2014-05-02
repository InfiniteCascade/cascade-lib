<?php
use yii\helpers\Url;
use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;
use infinite\web\grid\Grid;
$this->title = 'Dashboard';
$refreshable = [
    'url' => Url::to('app/stream'),
    'stream' => true,
    'data' => ['baseInstructions' => []]
];
$this->bodyHtmlOptions['data-refreshable'] = json_encode($refreshable);

echo Html::beginTag('div', ['class' => 'ic-dashboard row']);
Yii::beginProfile("Build Grid");
$widgets = Yii::$app->collectors['widgets']->getLocation('front');
ArrayHelper::multisort($widgets, ['priority', 'name'], [SORT_ASC, SORT_ASC]);
$grid = new Grid;
$cells = [];
Yii::beginProfile("Collect Widgets");
foreach ($widgets as $item => $widget) {
    $cells[] = Yii::$app->collectors['widgets']->build(null, $widget, [], []);
}
Yii::endProfile("Collect Widgets");
$grid->cells = $cells;
Yii::endProfile("Build Grid");
Yii::beginProfile("Render Grid");
echo $grid->generate();
Yii::endProfile("Render Grid");
echo Html::endTag('div');
