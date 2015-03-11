<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
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
