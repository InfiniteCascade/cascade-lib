<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * AclRole is the model class for table "acl_role".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AclRole extends \infinite\db\models\AclRole
{
    use ActiveRecordTrait;
    /**
     * @inheritdoc
     */
    public static $queryClass = 'cascade\\models\\AclRoleQuery';
}
