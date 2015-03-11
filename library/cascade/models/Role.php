<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

/**
 * Role is the model class for table "role".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Role extends \teal\db\models\Role
{
    use ActiveRecordTrait {
        behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), self::baseBehaviors(), []);
    }
}
