<?php
use infinite\helpers\Html;
use yii\widgets\ActiveForm;

$specialRequestors = $access->specialRequestors;
$primaryAccount = false;
if (isset($specialRequestors['primaryAccount'])) {
	$primaryAccount = $specialRequestors['primaryAccount'];
}
$publicGroup = $specialRequestors['public'];

// collect roles
$objectRoles = $access->roleObjects;
$object = Yii::$app->request->object;
$roles = $access->getPossibleRoles();

$baseRequestorParams = [
		'objectRoles' => $objectRoles,
		'objectAccess' => $access,
		'disableFields' => $disableFields,
		'roles' => $roles,
];
$htmlOptions = ['class' => 'list-group ic-access-list'];

if (!$disableFields) {
	$dataAccess = [];
	$dataAccess['roles'] = $roles;
	$htmlOptions['data-access'] = json_encode($dataAccess);
}

$form = ActiveForm::begin(['options' => ['class' => 'ajax']]); 

echo Html::beginTag('ul', $htmlOptions);
if ($publicGroup) {
	$requestorParams = [
		'requestorObject' => $publicGroup,
		'helpText' => 'Access level for the public',
		'maxRoleLevel' => 199,
		'htmlOptions' => ['class' => 'list-group-item-warning']
	];
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, $requestorParams), $this);
	unset($objectRoles[$publicGroup->primaryKey]);
}

if ($primaryAccount) {
	$requestorParams = [
		'requestorObject' => $primaryAccount,
		'helpText' => 'Access level for internal staff',
		'maxRoleLevel' => 399,
		'htmlOptions' => ['class' => 'list-group-item-info']
	];
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, $requestorParams), $this);
	unset($objectRoles[$primaryAccount->primaryKey]);
}

foreach ($objectRoles as $objectId => $objectRole) {
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, ['requestorObject' => $objectRole['object']]), $this);
}
echo Html::endTag('ul');
ActiveForm::end();
?>