<?php
use infinite\helpers\Html;
use yii\helpers\Url;
echo Html::beginTag('div', ['class' => 'panel panel-default']);
echo Html::beginTag('div', ['class' => 'panel panel-heading']);
echo Html::tag('h3', $object->descriptor, ['class' => 'panel-title']);
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'panel-body']);
echo Html::tag('div', 'Please choose the object you wish to view '. Html::tag('em', $object->descriptor) .' through.', ['class' => 'alert alert-info']);
echo Html::beginTag('div', ['class' => 'list-group']);
foreach ($options as $option) {
	echo Html::a($option['descriptor'], $option['url'], ['class' => 'list-group-item']);
}
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endTag('div');