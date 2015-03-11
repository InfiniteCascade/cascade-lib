<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * Aca is the model class for table "aca".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Aca extends \teal\db\models\Aca
{
    use ActiveRecordTrait {
        behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), self::baseBehaviors(), []);
    }
}
