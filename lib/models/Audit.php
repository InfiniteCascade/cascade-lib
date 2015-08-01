<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;
use Yii;

/**
 * Audit is the model class for table "audit".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Audit extends \canis\db\models\Audit
{
    use ActiveRecordTrait;

    /**
     * [[@doctodo method_description:activityDataProvider]].
     *
     * @param array $dataProvider [[@doctodo param_description:dataProvider]] [optional]
     *
     * @return [[@doctodo return_type:activityDataProvider]] [[@doctodo return_description:activityDataProvider]]
     */
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['data_interface_id'], 'string', 'max' => 36],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'data_interface_id' => 'Data Interface',
        ]);
    }

    /**
     * Get data interface.
     *
     * @return [[@doctodo return_type:getDataInterface]] [[@doctodo return_description:getDataInterface]]
     */
    public function getDataInterface()
    {
        return $this->hasOne(Yii::$app->classes['DataInterface'], ['id' => 'data_interface_id']);
    }
}
