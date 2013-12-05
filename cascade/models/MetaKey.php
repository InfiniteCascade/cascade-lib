<?php

namespace cascade\models;

/**
 * This is the model class for table "meta_key".
 *
 * @property string $id
 * @property string $name
 * @property string $value_type
 * @property string $created
 * @property string $modified
 *
 * @property Meta[] $metas
 * @property Registry $id
 */
class MetaKey extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'meta_key';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'value_type'], 'required'],
			[['created', 'modified'], 'safe'],
			[['id'], 'string', 'max' => 36],
			[['name', 'value_type'], 'string', 'max' => 255]
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
			'value_type' => 'Value Type',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getMetas()
	{
		return $this->hasMany(MetaKey::className(), ['meta_key_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getId()
	{
		return $this->hasOne(Registry::className(), ['id' => 'id']);
	}
}
