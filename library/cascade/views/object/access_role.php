<?php
use infinite\helpers\Html;

if (empty($role)) {
	$role = 'null';
} elseif (is_object($role)) {
	$role = $role->primaryKey;
}
$selectedRole = isset($roles[$role]) ? $roles[$role] : $roles['null'];
$isDisabled = $disableFields || !$selectedRole['available'];
$htmlOptions = ['type' => 'button'];
Html::addCssClass($htmlOptions, 'btn btn-default pull-right');
if ($isDisabled) {
	Html::addCssClass($htmlOptions, 'disabled');
}
echo Html::tag('button', $selectedRole['label'] .' '. Html::tag('span', '', ['class' => 'caret']), $htmlOptions);
?>