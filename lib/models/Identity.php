<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

/**
 * Identity is the model class for table "identity".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Identity extends \canis\db\models\Identity
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
