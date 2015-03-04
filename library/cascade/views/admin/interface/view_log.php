<?php
use infinite\helpers\Html;

\infinite\web\assetBundles\LogAsset::register($this);
$interfaceModel = $dataInterfaceLog->dataInterface;
$this->title = "View Data Interface Log";
$this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Interfaces', 'url' => ['admin/interface/index']];
$this->params['breadcrumbs'][] = ['label' => $interfaceModel->name, 'url' => ['admin/interface/view-logs', 'id' => $interfaceModel->primaryKey]];

$this->params['breadcrumbs'][] = $this->title;

echo Html::tag('div', '', [
    'data-log' => json_encode($dataInterfaceLog->dataPackage),
]);
