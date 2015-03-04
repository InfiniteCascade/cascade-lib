<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\security;

use Yii;

/**
 * ObjectAccess [@doctodo write class description for ObjectAccess].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ObjectAccess extends \infinite\security\ObjectAccess
{
    /**
     * @var __var_specialAuthorities_type__ __var_specialAuthorities_description__
     */
    public $specialAuthorities = ['Group'];

    /**
     * @inheritdoc
     */
    public function determineVisibility()
    {
        $groupClass = Yii::$app->classes['Group'];
        $groupPrefix = $groupClass::modelPrefix();
        $publicGroup = Yii::$app->gk->publicGroup;
        $primaryAccount = Yii::$app->gk->primaryAccount;
        $actions = Yii::$app->gk->actionsByName;
        $readAction = $actions['read'];
        $publicAro = isset($this->requestors[$publicGroup->primaryKey]) ? $this->requestors[$publicGroup->primaryKey] : false;
        $primaryAccountAro = ($primaryAccount && isset($this->requestors[$primaryAccount->primaryKey])) ? $this->requestors[$primaryAccount->primaryKey] : false;

        if ($publicAro && $publicAro[$readAction->primaryKey]->can($publicAro)) {
            return 'public';
        }

        if ($primaryAccountAro && $primaryAccountAro[$readAction->primaryKey]->can($primaryAccount)) {
            return 'internal';
        }
        $foundOwner = false;

        foreach ($this->roles as $role) {
            if (empty($role['role_id'])) {
                continue;
            }
            $roleItem = Yii::$app->collectors['roles']->getById($role['role_id']);
            if (empty($roleItem) || empty($roleItem->object)) {
                continue;
            }
            if ($roleItem->levelSection === 'owner') {
                $foundOwner = true;
                continue;
            }

            return 'shared';
        }
        if ($foundOwner) {
            return 'private';
        } else {
            return 'admins';
        }
    }

    /**
     * @inheritdoc
     */
    public function getRoleHelpText($roleItem)
    {
        return $this->object->objectType->getRoleHelpText($roleItem, $this->object);
    }

    /**
     * @inheritdoc
     */
    public function getSpecialRequestors()
    {
        return array_merge(parent::getSpecialRequestors(), [
            'primaryAccount' => [
                'object' =>    Yii::$app->gk->primaryAccount,
                'maxRoleLevel' => Yii::$app->params['maxRoleLevels']['primaryAccount'],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function validateRole($role, $validationSettings)
    {
        $results = parent::validateRole($role, $validationSettings);
        $objectType = $validationSettings['object']->objectType;
        if (!empty($role) && $role !== 'none' && !in_array($objectType->systemId, $this->specialAuthorities) && $objectType->getBehavior('Authority') === null) {
            $results['errors'][] = $validationSettings['object']->descriptor.' can not be shared with.';
        }

        return $results;
    }

    /**
     * @inheritdoc
     */
    protected function fillValidationSettings($validationSettings)
    {
        if (isset($validationSettings['object'])) {
            $objectType = $validationSettings['object']->objectType;
            $objectTypeSettings = $objectType->getRoleValidationSettings($validationSettings['object']);
            foreach ($objectTypeSettings as $key => $value) {
                switch ($key) {
                    case 'maxRoleLevel':
                        if (isset($validationSettings[$key]) && $validationSettings[$key] !== true) {
                            $validationSettings[$key] = min($validationSettings[$key], $value);
                        } else {
                            $validationSettings[$key] = $value;
                        }
                    break;
                    case 'possibleRoles':
                        if (isset($validationSettings[$key]) && $validationSettings[$key] !== true) {
                            $validationSettings[$key] = array_intersect($validationSettings[$key], $value);
                        } else {
                            $validationSettings[$key] = $value;
                        }
                    break;
                    default:
                        $validationSettings[$key] = $value;
                    break;
                }
            }
        }

        return $validationSettings;
    }
}
