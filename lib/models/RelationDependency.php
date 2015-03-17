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
 * RelationDependency is the model class for table "relation_dependency".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationDependency extends \canis\db\models\RelationDependency
{
    use ActiveRecordTrait;
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
        return [];
    }
}
