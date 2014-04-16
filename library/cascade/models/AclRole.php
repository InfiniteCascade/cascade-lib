<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

class AclRole extends \infinite\db\models\AclRole
{
    use ActiveRecordTrait;
    public static $queryClass = 'cascade\\models\\AclRoleQuery';
}
