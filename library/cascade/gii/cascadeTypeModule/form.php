<?php
/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\module\Generator $generator
 */

use infinite\helpers\Html;

infinite\web\assetBundles\FontAwesomeAsset::register($this);
infinite\web\assetBundles\UnderscoreAsset::register($this);

$js = <<< END

$("#generator-icon option").each(function() {
	$(this).html($(this).text());
	console.log($(this).text());
});
END;
	Html::registerJsBlock($js);
$css = <<< END
#generator-icon option {
	font-family: 'FontAwesome';
}
END;
	$this->registerCss($css);
	echo \yii\helpers\Html::activeHiddenInput($generator, 'migrationTimestamp');
?>
<div class="module-form">
<?php

//	echo $form->field($generator, 'moduleName');

//	echo $form->field($generator, 'moduleClass');
//	echo $form->field($generator, 'moduleID');

	echo $form->field($generator, 'baseNamespace');
	echo $form->field($generator, 'tableName');
	echo $form->field($generator, 'descriptorField');
	echo $form->field($generator, 'title');
	echo $form->field($generator, 'section')->dropDownList($generator->possibleSections());


	echo '<div class="ic-icon-preview"></div>';
	echo $form->field($generator, 'icon')->dropDownList($generator->possibleIcons(), ['style' => "font-family: 'FontAwesome'; font-size: 2em; height: auto; -webkit-appearance: none;"]);

	

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
