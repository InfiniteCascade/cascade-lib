<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * Registry is the model class for table "registry".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Registry extends \canis\db\models\Registry
{
    use ActiveRecordTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Relatable' => [
                'class' => 'canis\db\behaviors\Relatable',
            ],
        ]);
    }
}
