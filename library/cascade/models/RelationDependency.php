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
 * RelationDependency is the model class for table "relation_dependency".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationDependency extends \teal\db\models\RelationDependency
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
