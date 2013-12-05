<?php
/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\module\Generator $generator
 */

\infinite\web\IconAsset::register($this);

use \infinite\helpers\Html;
$js = <<< END
$("#generator-icon").bind('change keyup', function(event) {
	$('.ic-icon-preview').replaceWith($('<div />').css({'float': 'right', 'margin': '5px'}).addClass('ic-icon-32 ic-icon-black ic-icon ic-icon-preview '+ $(this).val()));
	event.stopPropagation();
}).trigger('keyup');
END;
	Html::registerJsBlock($js);
	echo \yii\helpers\Html::activeHiddenInput($generator, 'migrationTimestamp');
?>
<div class="module-form">
<?php

//	echo $form->field($generator, 'moduleName');

//	echo $form->field($generator, 'moduleClass');
//	echo $form->field($generator, 'moduleID');

	echo $form->field($generator, 'baseNamespace');
	echo $form->field($generator, 'tableName');
	echo $form->field($generator, 'title');

	echo '<div class="ic-icon-preview"></div>';
	echo $form->field($generator, 'icon')->dropDownList($generator->possibleIcons());

	

	echo $form->field($generator, 'parents');
	echo $form->field($generator, 'children');


	echo $form->field($generator, 'uniparental')->checkbox();
	echo $form->field($generator, 'hasDashboard')->checkbox();

//	echo $form->field($generator, 'modelClass');
//	echo $form->field($generator, 'ns');
//	echo $form->field($generator, 'baseClass');
//	echo $form->field($generator, 'db');
//	echo $form->field($generator, 'generateRelations')->checkbox();
//	echo $form->field($generator, 'generateLabelsFromComments')->checkbox();

?>
</div>
