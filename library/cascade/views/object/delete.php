<?php
use infinite\helpers\Html;
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'form']);
$model->confirm = 1;
echo Html::activeHiddenInput($model, 'confirm');

if (count($model->possibleTargets) === 1) {
    $label = $model->labels[$model->possibleTargets[0]];
    echo '<div class="confirm">Are you sure you want with '. $label['long'] .'?</div>';
} else {
    $parts = [];
    $parts[] = 'the '. $model->object->objectType->title->getSingular(false) . ' <em>'. $model->object->descriptor .'</em>';
    if ($model->hasRelationshipTargets()) {
        $parts[] = 'its relationship with the '. $model->relationshipWith->objectType->title->getSingular(false) . ' <em>'. $model->relationshipWith->descriptor .'</em>';
    }
    echo '<div class="confirm">What would you like to do to '. implode($parts, ' and ') .'?</div>';
    echo '<div class="btn-group" data-toggle="buttons">';
    $itemOptions = ['container' => false];
    foreach ($model->possibleTargets as $target) {
        $label = $model->labels[$target];
        $labelOptions = isset($label['options']) ? $label['options'] : [];
        if (!isset($labelOptions['class'])) {
            Html::addCssClass($labelOptions, 'btn btn-default');
        } else {
            Html::addCssClass($labelOptions, 'btn');
        }
        if ($model->target === $target) {
            Html::addCssClass($labelOptions, 'active');
        }
        $labelOptions['title'] = ucfirst(strip_tags($label['long']));
        echo Html::activeRadio($model, 'target', array_merge($itemOptions, [
            'value' => $target,
            'label' => $label['short'],
            'labelOptions' => $labelOptions
        ]));
    }
    echo '</div>';
}
return;

\d($model->possibleTargets);exit;

if (is_null($model->relationModel) || $model->forceObjectDelete) {
    echo "<div class='confirm'>Are you sure you want to delete the ".$model->object->objectType->title->getSingular(false)." <em>". $model->object->descriptor ."</em>?</div>";
} else {
    if (!empty($model->forceRelationshipDelete)) {
        echo "<div class='confirm'>Are you sure you want to delete the relationship between ".$model->object->objectType->title->getSingular(false)." <em>". $model->object->descriptor ."</em> and  <em>".$model->relationshipWith->descriptor."</em>?</div>";
    } else {

        echo "<div class='confirm'>Do you want to delete the ".$model->object->objectType->title->getSingular(false)." <em>". $model->object->descriptor ."</em> <strong>or</strong> its relationship with <em>".$model->relationshipWith->descriptor."</em>?</div>";
        echo '<div class="btn-group" data-toggle="buttons">';
        $itemOptions = ['container' => false];
        echo Html::radio('target', $model->target === 'relationship', array_merge($itemOptions, [
                    'value' => 'relationship',
                    'label' => 'Relationship',
                    'labelOptions' => ['class' => 'btn btn-default' . ($model->target === 'relationship' ? ' active' : '')]
                ]));
        echo Html::radio('target', $model->target === 'object', array_merge($itemOptions, [
                    'value' => 'object',
                    'label' => $model->object->objectType->title->getSingular(true),
                    'labelOptions' => ['class' => 'btn btn-danger'. ($model->target === 'object' ? ' active' : '')]
                ]));
        echo '</div>';
     }
}
echo Html::endTag('div');
echo Html::endForm();
