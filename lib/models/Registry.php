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
 * Registry is the model class for table "registry".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Registry extends \teal\db\models\Registry
{
    use ActiveRecordTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Relatable' => [
                'class' => 'teal\db\behaviors\Relatable',
            ],
        ]);
    }
}
