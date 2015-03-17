<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * AclRole is the model class for table "acl_role".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AclRole extends \canis\db\models\AclRole
{
    use ActiveRecordTrait;
    /**
     * @inheritdoc
     */
    public static $queryClass = 'cascade\models\AclRoleQuery';
}
