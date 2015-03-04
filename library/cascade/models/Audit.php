<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use Yii;
use cascade\components\db\ActiveRecordTrait;

/**
 * Audit is the model class for table "audit".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Audit extends \infinite\db\models\Audit
{
    use ActiveRecordTrait;

    public static function activityDataProvider($dataProvider = [])
    {
        $default = [
        ];
        $dataProvider = array_merge_recursive($default, $dataProvider);
        if (!isset($dataProvider['class'])) {
            $dataProvider['class'] = 'cascade\components\db\behaviors\auditable\AuditDataProvider';
        }

        $dataProvider['query'] = static::find();

        return Yii::createObject($dataProvider);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['data_interface_id'], 'string', 'max' => 36],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'data_interface_id' => 'Data Interface',
        ]);
    }

    public function getDataInterface()
    {
        return $this->hasOne(Yii::$app->classes['DataInterface'], ['id' => 'data_interface_id']);
    }
}
