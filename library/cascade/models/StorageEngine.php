<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use Yii;
use cascade\components\types\ActiveRecordTrait;

/**
 * StorageEngine is the model class for table "storage_engine".
 *
 * @property string $id
 * @property string $handler
 * @property string $data
 * @property string $created
 * @property string $modified
 * @property Registry $registry
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class StorageEngine extends \cascade\components\db\ActiveRecord
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

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'storage_engine';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data'], 'string'],
            [['created', 'modified'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['handler'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'handler' => 'Handler',
            'data' => 'Data',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * Get registry.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getRegistry()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
    }

    /**
     * Get storage handler.
     *
     * @return __return_getStorageHandler_type__ __return_getStorageHandler_description__
     */
    public function getStorageHandler()
    {
        if (Yii::$app->collectors['storageHandlers']->has($this->handler)) {
            return Yii::$app->collectors['storageHandlers']->getOne($this->handler);
        }

        return false;
    }
}
