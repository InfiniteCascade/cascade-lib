<?php

namespace cascade\models;

/**
 * This is the model class for table "key_translation".
 *
 * @property string $id
 * @property string $data_interface_id
 * @property string $registry_id
 * @property string $key
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
 * @property DataInterface $dataInterface
 */
class KeyTranslation extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'key_translation';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['key'], 'required'],
			[['created', 'modified'], 'safe'],
			[['data_interface_id', 'registry_id'], 'string', 'max' => 36],
			[['key'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'data_interface_id' => 'Data Interface ID',
			'registry_id' => 'Registry ID',
			'key' => 'Key',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getRegistry()
	{
		return $this->hasOne(Registry::className(), ['id' => 'registry_id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getDataInterface()
	{
		return $this->hasOne(DataInterface::className(), ['id' => 'data_interface_id']);
	}
}
