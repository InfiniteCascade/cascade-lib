<?php

namespace cascade\models;

/**
 * This is the model class for table "storage".
 *
 * @property string $id
 * @property string $storage_engine_id
 * @property string $storage_key
 * @property string $name
 * @property string $file_name
 * @property string $type
 * @property string $size
 * @property string $created
 * @property string $modified
 *
 * @property ObjectFile[] $objectFiles
 * @property Registry $registry
 * @property StorageEngineId $storageEngine
 */
class Storage extends \cascade\components\db\ActiveRecord
{
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
			[['storage_engine_id', 'file_name', 'type', 'size'], 'required'],
			[['size'], 'integer'],
			[['created', 'modified'], 'safe'],
			[['id', 'storage_engine_id'], 'string', 'max' => 36],
			[['storage_key', 'name', 'file_name'], 'string', 'max' => 255],
			[['type'], 'string', 'max' => 100]
		];
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
			'name' => 'Name',
			'file_name' => 'File Name',
			'type' => 'Type',
			'size' => 'Size',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getObjectFiles()
	{
		return $this->hasMany(ObjectFile::className(), ['storage_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getRegistry()
	{
		return $this->hasOne(Registry::className(), ['id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getStorageEngine()
	{
		return $this->hasOne(StorageEngineId::className(), ['id' => 'storage_engine_id']);
	}
}
