<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

class Task_000006_acl extends AclTask
{
    public function getBaseRules()
    {
        return [
        // @todo add primary account
        ];
    }
}
