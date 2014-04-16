<?php
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
