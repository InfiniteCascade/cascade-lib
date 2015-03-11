<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db;

/**
 * ActiveRecord is the model class for table "{{%active_record}}".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveRecord extends \teal\db\ActiveRecord
{
    use ActiveRecordTrait;
    /**
     * @inheritdoc
     */
    public static $queryClass = 'cascade\components\db\ActiveQuery';
}
