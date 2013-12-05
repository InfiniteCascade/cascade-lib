<?php

namespace cascade\models;

/**
 * This is the model class for table "object_type".
 *
 * @property string $name
 * @property double $system_version
 * @property string $created
 * @property string $modified
 */
class ObjectType extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_type';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name'], 'required'],
			[['system_version'], 'number'],
			[['created', 'modified'], 'safe'],
			[['name'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'name' => 'Name',
			'system_version' => 'System Version',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}
}
