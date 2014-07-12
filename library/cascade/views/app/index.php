<?php
use yii\helpers\Url;
use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;
use infinite\web\grid\Grid;
use infinite\web\grid\Cell;

$js = [];
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

$topGrid = new Grid;
$watchingWidget = Yii::$app->collectors['widgets']->getOne('WatchingContent');
$watchingCell = Yii::$app->collectors['widgets']->build(null, $watchingWidget, [], ['columns' => 6]);
$topGrid->currentRow->addCell($watchingCell);
$itemsCell = $topGrid->currentRow->addCell(new Cell(['columns' => 6]));
$widgetGrid = new Grid;
Html::addCssClass($widgetGrid->htmlOptions, 'ic-front-side');
$js[] = '$("#'. $widgetGrid->id .'").cascadeAffix();';

$widgetGrid->baseRow = ['trueWidth' => 6];
$cells = [];
Yii::beginProfile("Collect Widgets");
foreach ($widgets as $item => $widget) {
    $cells[] = Yii::$app->collectors['widgets']->build(null, $widget, [], []);
}
Yii::endProfile("Collect Widgets");
$widgetGrid->cells = $cells;
Yii::endProfile("Build Grid");
$itemsCell->content = $widgetGrid->generate();

echo $topGrid->generate();
echo Html::endTag('div');
$this->registerJs(implode("\n", $js));
