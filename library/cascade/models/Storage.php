<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;
/**
 * Storage is the model class for table "storage".
 *
 * @property string $id
 * @property string $storage_engine_id
 * @property string $storage_key
 * @property string $file_name
 * @property string $type
 * @property string $size
 * @property string $created
 * @property string $modified
 *
 * @property ObjectFile[] $objectFiles
 * @property Registry $registry
 * @property StorageEngineId $storageEngine
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Storage extends \cascade\components\db\ActiveRecord
{
    use ActiveRecordTrait {
        behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'storage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['storage_engine_id', 'file_name', 'type', 'size'], 'safe'],
            [['storage_engine_id', 'file_name', 'type', 'size'], 'required', 'on' => 'fill'],
            [['size'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['id', 'storage_engine_id'], 'string', 'max' => 36],
            [['storage_key', 'file_name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 100]
        ];
    }

    /**
     * __method_fillKill_description__
     * @param  __param_attributes_type__ $attributes __param_attributes_description__
     * @return __return_fillKill_type__  __return_fillKill_description__
     */
    public function fillKill($attributes)
    {
        if ($attributes === false) {
            $this->delete();

            return false;
        } elseif ($attributes !== true) {
            $this->scenario = 'fill';
            $this->attributes = $attributes;
            if (!$this->save()) {
                $this->delete();

                return false;
            }

            return true;
        } else {
            return true;
        }
    }

    /**
     * __method_startBlank_description__
     * @param  __param_engine_type__      $engine __param_engine_description__
     * @return __return_startBlank_type__ __return_startBlank_description__
     */
    public static function startBlank($engine)
    {
        $className = self::className();
        $blank = new $className;
        $blank->storage_engine_id = $engine->primaryKey;
        if ($blank->save()) {
            return $blank;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'storage_engine_id' => 'Storage Engine ID',
            'storage_key' => 'Storage Key',
            'file_name' => 'File Name',
            'type' => 'Type',
            'size' => 'Size',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * __method_getObjectFiles_description__
     * @return \yii\db\ActiveRelation
     */
    public function getObjectFiles()
    {
        return $this->hasMany(ObjectFile::className(), ['storage_id' => 'id']);
    }

    /**
     * __method_getRegistry_description__
     * @return \yii\db\ActiveRelation
     */
    public function getRegistry()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
    }

    /**
     * __method_getStorageEngine_description__
     * @return \yii\db\ActiveRelation
     */
    public function getStorageEngine()
    {
        return $this->hasOne(StorageEngine::className(), ['id' => 'storage_engine_id']);
    }
}
