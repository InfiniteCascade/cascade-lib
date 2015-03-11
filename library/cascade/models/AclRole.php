<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * AclRole is the model class for table "acl_role".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AclRole extends \teal\db\models\AclRole
{
    use ActiveRecordTrait;
    /**
     * @inheritdoc
     */
    public static $queryClass = 'cascade\models\AclRoleQuery';
}
