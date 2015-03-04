<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;

/**
 * Group is the model class for table "group".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Group extends \infinite\db\models\Group
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
