<?php
use infinite\helpers\Html;

cascade\components\web\assetBundles\ObjectViewAsset::register($this);

if (!isset($htmlOptions)) {
	$htmlOptions = [];
}
$roleSet = isset($objectRoles[$requestorObject->primaryKey]) ? $objectRoles[$requestorObject->primaryKey] : [];
$role = isset($roleSet['role']) ? $roleSet['role'] : null;
$roleObject = isset($role) ? $role->object : null;
Html::addCssClass($htmlOptions, 'list-group-item');
$requestorOptions = [];
$requestorOptions['id'] = $requestorObject->primaryKey;
$requestorOptions['label'] = $requestorObject->descriptor;
$requestorOptions['maxRoleLevel'] = isset($maxRoleLevel) ? $maxRoleLevel : false;
$requestorOptions['type'] = $requestorObject->objectType->systemId;
$requestorOptions['editable'] = true;
if (isset($role) && isset($roleSet['inherited']) && $roleSet['inherited'] && !$role->inheritedEditable) {
	$requestorOptions['editable'] = false;
}
$htmlOptions['data-requestor'] = json_encode($requestorOptions);
if (!$requestorOptions['editable']) {
	Html::addCssClass($htmlOptions, 'uneditable');
}
echo Html::beginTag('li', $htmlOptions);
echo $this->renderFile('@cascade/views/object/access_role.php', [
	'roles' => $roles,
	'role' => $roleObject,
	'objectAccess' => $objectAccess,
	'disableFields' => $disableFields,
	'editable' => $requestorOptions['editable']
], $this);
echo Html::tag('h4', $requestorObject->descriptor, ['class' => 'list-group-item-heading']);
if (!isset($helpText)) {
	$helpText = $requestorObject->objectType->title->upperSingular;
}
echo Html::tag('p', $helpText, ['class' => 'list-group-item-text help-text']);
if (isset($errors[$requestorObject->primaryKey])) {
	echo Html::tag('p', $errors[$requestorObject->primaryKey], ['class' => 'alert alert-danger']);
}
echo Html::endTag('li');
?>