<?php
/**
 * @var yii\base\View
 */
use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;

ArrayHelper::multisort($tasks, 'title');
$this->title = 'Maintenance Tasks';
$this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];
echo Html::pageHeader($this->title);
echo Html::beginTag('div', ['class' => 'list-group']);
foreach ($tasks as $taskId => $task) {
    echo Html::a(
        Html::tag('h4', $task['title'], ['class' => 'list-group-item-heading']).
        Html::tag('div', $task['description'], ['class' => 'list-group-item-text']),
        ['/admin/dashboard/tasks', 'task' => $taskId], ['class' => 'list-group-item', 'data-handler' => 'background']);
}

echo Html::endTag('div');
