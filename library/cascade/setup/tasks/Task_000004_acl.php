<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

use cascade\models\User;

/**
 * Task_000004_acl [@doctodo write class description for Task_000004_acl].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Task_000004_acl extends AclTask
{
    /**
     * @inheritdoc
     */
    public function getBaseRules()
    {
        return [
            [
                'action' => null,
                'controlled' => null,
                'accessing' => ['model' => 'cascade\models\Group', 'fields' => ['system' => 'administrators']],
                'object_model' => null,
                'task' => 'allow',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function test()
    {
        $run = false; // User::find()->disableAccessCheck()->andWhere(['and', ['email' => 'ema']])->count() > 0;

        return $run && parent::test();
    }
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!User::systemUser()) {
            return false;
        }

        return parent::run();
    }
}
