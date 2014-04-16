<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

use cascade\models\Group;
use cascade\models\User;
use infinite\base\exceptions\Exception;

class Task_000007_admin_user extends \infinite\setup\Task
{
    public function getTitle()
    {
        return 'Admin User';
    }

    public function test()
    {
        return User::find()->disableAccessCheck()->andWhere(['not', ['username' => 'system']])->count() > 0;
    }

    public function run()
    {
        $user = new User;
        $user->scenario = 'creation';
        $user->attributes = $this->input['admin'];
        $user->status = User::STATUS_ACTIVE;
        $superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
        if (!$superGroup) {
            throw new Exception("Unable to find super_administrators group!");
        }
        $user->relationModels = [['parent_object_id' => $superGroup->primaryKey]];
        if ($user->save()) {
            return true;
        }
        foreach ($user->errors as $field => $errors) {
            $this->fieldErrors[$field] = implode('; ', $errors);
        }
        var_dump($this->fieldErrors);exit;

        return false;
    }

    public function getFields()
    {
        $fields = [];
        $fields['admin'] = ['label' => 'First Admin User', 'fields' => []];
        $fields['admin']['fields']['first_name'] = ['type' => 'text', 'label' => 'First Name', 'required' => true, 'value' => function () { return 'Super'; }];
        $fields['admin']['fields']['last_name'] = ['type' => 'text', 'label' => 'Last Name', 'required' => true, 'value' => function () { return 'Admin'; }];
        $fields['admin']['fields']['username'] = ['type' => 'text', 'label' => 'Username', 'required' => true, 'value' => function () { return 'admin'; }];
        $fields['admin']['fields']['password'] = ['type' => 'text', 'label' => 'Password', 'required' => true, 'value' => function () { return 'adminadmin'; }];

        return $fields;
    }
}
