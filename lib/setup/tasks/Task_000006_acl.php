<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\setup\tasks;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Task_000006_acl extends AclTask
{
    /**
     * @inheritdoc
     */
    public function getBaseRules()
    {
        return [
        // @todo add primary account
        ];
    }
}
