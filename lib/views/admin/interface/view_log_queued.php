<?php
use teal\helpers\Html;

$this->registerMetaTag(['http-equiv' => 'refresh', 'content' => '5']);

$interfaceModel = $dataInterfaceLog->dataInterface;
$this->title = "View Data Interface Log";
$this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Interfaces', 'url' => ['admin/interface/index']];
$this->params['breadcrumbs'][] = ['label' => $interfaceModel->name, 'url' => ['admin/interface/view-logs', 'id' => $interfaceModel->primaryKey]];
$this->params['breadcrumbs'][] = $this->title;
echo Html::tag('div', 'Please wait while your data interface import task is in the queue.', ['class' => 'alert alert-warning']);
