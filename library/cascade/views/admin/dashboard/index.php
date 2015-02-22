<?php
/**
 * @var yii\base\View $this
 */
use infinite\helpers\Html;
$this->title = 'Administration';
$this->params['breadcrumbs'][] = ['label' => $this->title];
echo Html::beginTag('div', ['class' => 'list-group']);

echo Html::a(
    Html::tag('h4', 'Maintenance Tasks', ['class' => 'list-group-item-heading']).
    Html::tag('div', 'Run various maintenance tasks', ['class' => 'list-group-item-text']),
    ['/admin/dashboard/tasks'], ['class' => 'list-group-item']);

echo Html::a(
    Html::tag('h4', 'Interfaces', ['class' => 'list-group-item-heading']).
    Html::tag('div', 'Run various maintenance tasks', ['class' => 'list-group-item-text']),
    ['/admin/interface/index'], ['class' => 'list-group-item']);

echo Html::endTag('div');