<?php
use canis\helpers\Html;

$this->title = "<em>{$dataInterface->name}</em> Interface Logs";
$this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Interfaces', 'url' => ['admin/interface/index']];
$this->params['breadcrumbs'][] = $this->title;

echo Html::pageHeader($this->title);
echo Html::beginTag('div', ['class' => 'list-group']);
$logs = $dataInterface->getRelatedLogQuery()->orderBy(['created' => SORT_DESC])->all();
foreach ($logs as $log) {
    $itemHtmlOptions = ['class' => 'list-group-item'];
    Html::addCssClass($itemHtmlOptions, 'list-group-item-' . $log->bootstrapState);
    echo Html::a(date("F d, Y h:i:sa", strtotime($log->created)), ['admin/interface/view-log', 'id' => $log->id], $itemHtmlOptions);
}
echo Html::endTag('div');
