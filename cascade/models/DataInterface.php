<?php

namespace cascade\models;

/**
 * This is the model class for table "data_interface".
 *
 * @property string $id
 * @property string $name
 * @property string $system_id
 * @property string $last_sync
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id
 * @property DataInterfaceLog[] $dataInterfaceLogs
 * @property KeyTranslation[] $keyTranslations
 */
class DataInterface extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'data_interface';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'system_id'], 'required'],
			[['last_sync', 'created', 'modified'], 'safe'],
			[['id'], 'string', 'max' => 36],
			[['name', 'system_id'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'Name',
			'system_id' => 'System ID',
			'last_sync' => 'Last Sync',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getId()
	{
		return $this->hasOne(Registry::className(), ['id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getDataInterfaceLogs()
	{
		return $this->hasMany(DataInterface::className(), ['data_interface_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getKeyTranslations()
	{
		return $this->hasMany(DataInterface::className(), ['data_interface_id' => 'id']);
	}
}
