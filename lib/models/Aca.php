<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * Aca is the model class for table "aca".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Aca extends \canis\db\models\Aca
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
