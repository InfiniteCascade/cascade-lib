<?php
use infinite\helpers\Html;

$specialRequestors = $access->specialRequestors;
$primaryAccount = false;
if (isset($specialRequestors['primaryAccount'])) {
	$primaryAccount = $specialRequestors['primaryAccount'];
}
$publicGroup = $specialRequestors['public'];

// collect roles
$accessorRoleLevel = $access->getAccessorRoleLevel();
$objectRoles = $access->roleObjects;
$object = Yii::$app->request->object;
$roles = [];
$nullRole = [];
$nullRole['id'] = null;
$nullRole['item'] = null;
$nullRole['label'] = 'No Access';
$nullRole['available'] = 0 <= $accessorRoleLevel;
$roles['null'] = $nullRole;

foreach (Yii::$app->collectors['roles']->getAll() as $roleItem) {
	$role = [];
	$role['id'] = $roleItem->object->primaryKey;
	$role['exclusive'] = $roleItem->exclusive;
	$role['conflictRole'] = null;
	if ($roleItem->conflictRole 
		&& ($conflictRole = Yii::$app->collectors['roles']->getOne($roleItem->conflictRole))
		&& isset($conflictRole->object)) {
		$role['conflictRole'] = $conflictRole->object->primaryKey;
	}
	$role['label'] = $roleItem->name;
	$role['available'] = $roleItem->level <= $accessorRoleLevel;
	$roles[$role['id']] = $role;
}

$baseRequestorParams = [
		'objectRoles' => $objectRoles,
		'objectAccess' => $access,
		'disableFields' => $disableFields,
		'roles' => $roles,
];
$htmlOptions = ['class' => 'list-group ic-access-list'];

$dataAccess = [];
$dataAccess['roles'] = $roles;
$htmlOptions['data-access'] = json_encode($dataAccess);

echo Html::beginTag('ul', $htmlOptions);
if ($publicGroup) {
	$requestorParams = [
		'requestorObject' => $publicGroup,
		'helpText' => 'Access level for the public',
		'maxLevel' => 100,
		'htmlOptions' => ['class' => 'list-group-item-warning']
	];
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, $requestorParams), $this);
	unset($objectRoles[$publicGroup->primaryKey]);
}

if ($primaryAccount) {
	$requestorParams = [
		'requestorObject' => $primaryAccount,
		'helpText' => 'Access level for internal staff',
		'maxLevel' => 400,
		'htmlOptions' => ['class' => 'list-group-item-info']
	];
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, $requestorParams), $this);
	unset($objectRoles[$primaryAccount->primaryKey]);
}

foreach ($objectRoles as $objectId => $objectRole) {
	echo $this->renderFile('@cascade/views/object/access_requestor.php', array_merge($baseRequestorParams, ['requestorObject' => $objectRole['object']]), $this);
}
echo Html::endTag('ul');
?>