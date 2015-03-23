<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\setup\tasks;

use cascade\models\User;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Acl extends AclTask
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
