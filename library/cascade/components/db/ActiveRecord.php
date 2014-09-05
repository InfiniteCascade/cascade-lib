<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db;

/**
 * ActiveRecord is the model class for table "{{%active_record}}".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveRecord extends \infinite\db\ActiveRecord
{
    use ActiveRecordTrait;
    public static $queryClass = 'cascade\\components\\db\\ActiveQuery';
}
