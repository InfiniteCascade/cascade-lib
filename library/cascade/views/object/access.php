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
$object = Yii::$app->request->object;
$roles = $access->getPossibleRoles();

$baseRequestorParams = [
		'objectRoles' => $objectRoles,
		'objectAccess' => $access,
		'disableFields' => $disableFields,
		'roles' => $roles,
		'errors' => $errors,
];
$htmlOptions = ['class' => 'list-group ic-access-list'];

if (!$disableFields) {
	$types = [];
	foreach (Yii::$app->collectors['types']->getAll() as $typeItem) {
		if (!$typeItem->active) { continue; }
		$types[$typeItem->systemId] = [
			'label' => $typeItem->object->title->upperSingular,
			'possibleRoles' => $typeItem->object->possibleRoles,
			'initialRole' => $typeItem->object->initialRole,
			'requiredRoles' => $typeItem->object->requiredRoles
		];
	}
	$dataAccess = [];
	$dataAccess['roles'] = $roles;
	$dataAccess['types'] = $types;
	$dataAccess['universalMaxRoleLevel'] = $access->getUniversalMaxRoleLevel();
	$htmlOptions['data-access'] = json_encode($dataAccess);
}

$form = ActiveForm::begin(['options' => ['class' => 'ajax']]); 

echo Html::beginTag('ul', $htmlOptions);
if ($publicGroup) {
	$requestorParams = [
		'requestorObject' => $publicGroup['object'],
		'helpText' => 'Access level for the public',
		'maxRoleLevel' => $publicGroup['maxRoleLevel'],
		'htmlOptions' => ['class' => 'list-group-item-warning']
	];
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, $requestorParams), $this);
	unset($objectRoles[$publicGroup['object']->primaryKey]);
}

if ($primaryAccount) {
	$requestorParams = [
		'requestorObject' => $primaryAccount['object'],
		'helpText' => 'Access level for internal staff',
		'maxRoleLevel' => $primaryAccount['maxRoleLevel'],
		'htmlOptions' => ['class' => 'list-group-item-info']
	];
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, $requestorParams), $this);
	unset($objectRoles[$primaryAccount['object']->primaryKey]);
}

foreach ($objectRoles as $objectId => $objectRole) {
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, ['requestorObject' => $objectRole['object']]), $this);
}
echo Html::endTag('ul');
ActiveForm::end();
?>