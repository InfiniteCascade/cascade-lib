<?php
use infinite\helpers\Html;
echo Html::beginForm('', 'post', array('class' => 'ajax'));
echo Html::beginTag('div', array('class' => 'form'));
$model->confirm = 1;
echo Html::activeHiddenInput($model, 'confirm');
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
					'labelOptions' => ['class' => 'btn btn-warning']
				]));
	 	echo Html::radio('target', $model->target === 'object', array_merge($itemOptions, [
					'value' => 'object',
					'label' => $model->object->objectType->title->getSingular(true),
					'labelOptions' => ['class' => 'btn btn-danger']
				]));
	 	echo '</div>';
	 }
}
echo Html::endTag('div');
echo Html::endForm();
?>