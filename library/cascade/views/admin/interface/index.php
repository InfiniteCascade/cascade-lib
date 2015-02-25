<?php
use infinite\helpers\Html;

$this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

$this->title = 'Interfaces';
echo Html::pageHeader($this->title);
echo Html::beginTag('div', ['class' => 'list-group']);
foreach (Yii::$app->collectors['dataInterfaces']->getAll() as $interfaceItem) {
	$interface = $interfaceItem->object;
	$interfaceModel = $interfaceItem->interfaceObject;
	$lastLog = $interfaceModel->lastDataInterfaceLog;
	echo Html::beginTag('div', ['class' => 'list-group-item']);
	$items = [];
	$items[] = ['label' => 'Logs', 'url' => ['/admin/interface/view-logs', 'id' => $interfaceModel->primaryKey]];
	if (empty($lastLog) || !$lastLog->isActive) {
		$items[] = ['label' => 'Run', 'url' => ['/admin/interface/run', 'id' => $interfaceModel->primaryKey], 'htmlOptions' => ['data-handler' => 'background']];
	} else {
		$items[] = ['label' => 'View Active Log', 'url' => ['/admin/interface/view-log', 'id' => $lastLog->primaryKey]];
	}
	if (!empty($lastLog)) {
		$lastRanHtmlOptions = ['class' => 'label label-' . $lastLog->bootstrapState];
		$lastRan = Html::a(date("F d, Y h:ia", strtotime($lastLog->created)), ['/admin/interface/view-log', 'id' => $lastLog->primaryKey], $lastRanHtmlOptions);
	} else {
		$lastRan = Html::tag('span', 'Never Ran', ['class' => 'label label-warning']);
	}
	echo Html::tag('h4', $interface->name .' '. $lastRan . Html::buttonGroup($items, ['class' => 'pull-right btn-group-sm']));
	echo Html::endTag('div');
}
echo Html::endTag('div');
