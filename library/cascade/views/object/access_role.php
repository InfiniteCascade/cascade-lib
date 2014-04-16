<?php
use infinite\helpers\Html;

if (empty($role)) {
    $role = 'none';
} elseif (is_object($role)) {
    $role = $role->primaryKey;
}
$selectedRole = isset($roles[$role]) ? $roles[$role] : $roles['none'];
$isDisabled = $disableFields || !$selectedRole['available'] || !$editable;
$htmlOptions = ['type' => 'button'];
$roleOptions = $selectedRole;
$htmlOptions['data-role'] = json_encode($roleOptions);

Html::addCssClass($htmlOptions, 'btn btn-default pull-right');
if ($isDisabled) {
    Html::addCssClass($htmlOptions, 'disabled');
}
if (!$editable) {
    $selectedRole['label'] = 'Inherited '. $selectedRole['label'];
}
echo Html::tag('button', Html::tag('span', $selectedRole['label'], ['class' => 'role-label']) .' '. Html::tag('span', '', ['class' => 'caret']), $htmlOptions);
