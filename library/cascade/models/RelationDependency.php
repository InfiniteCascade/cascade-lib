<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

/**
 * Role is the model class for table "role".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationDependency extends \infinite\db\models\RelationDependency
{
    use ActiveRecordTrait;
    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }
    
    public function behaviors()
    {
        return [];
    }
}
