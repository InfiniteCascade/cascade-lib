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
 * IdentityProvider is the model class for table "identity_provider".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class IdentityProvider extends \teal\db\models\IdentityProvider
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
