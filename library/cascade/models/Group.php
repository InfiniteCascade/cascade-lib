<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;

/**
 * Group is the model class for table "group".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Group extends \teal\db\models\Group
{
    use TypesActiveRecordTrait {
        TypesActiveRecordTrait::behaviors as typesBehaviors;
    }

    use BaseActiveRecordTrait {
        BaseActiveRecordTrait::behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), self::baseBehaviors(), self::typesBehaviors(), []);
    }
}
