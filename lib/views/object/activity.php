<?php
use canis\helpers\Html;
use yii\helpers\Url;

$instructions = [];
$instructions['ajax']  = ['url' => Url::to(['/app/activity'])];
$instructions['scope'] = 'object';
$instructions['limit'] = 7;
$instructions['object'] = $object->primaryKey;
echo Html::beginTag('div', ['class' => 'ic-object-activity-viewport']);
echo Html::tag('div', '', ['data-activity-feed' => json_encode($instructions)]);
echo Html::tag('div', str_repeat(Html::tag('div', '', ['class' => 'widget-lazy-placeholder']), 5), ['class' => 'activity-feed-thinking']);
echo Html::endTag('div');
