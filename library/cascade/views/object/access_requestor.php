<?php
use infinite\helpers\Html;
if (!isset($htmlOptions)) {
	$htmlOptions = [];
}
Html::addCssClass($htmlOptions, 'list-group-item');
echo Html::beginTag('li', $htmlOptions);
echo $this->renderFile('@cascade/views/object/access_role.php', [
	'roles' => $roles,
	'role' => $objectRoles[$requestorObject->primaryKey]['role'],
	'objectAccess' => $objectAccess,
	'disableFields' => $disableFields
], $this);
echo Html::tag('h4', $requestorObject->descriptor, ['class' => 'list-group-item-heading']);
if (!isset($helpText)) {
	$helpText = $requestorObject->objectType->title->upperSingular;
}
echo Html::tag('p', $helpText, ['class' => 'list-group-item-text help-text']);
echo Html::endTag('li');
?>