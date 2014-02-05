<?php
namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

/**
 * This is the model class for table "storage_engine".
 *
 * @property string $id
 * @property string $handler
 * @property string $data
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
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
			[['handler'], 'string', 'max' => 255]
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
	 * @return \yii\db\ActiveRelation
	 */
	public function getRegistry()
	{
		return $this->hasOne(Registry::className(), ['id' => 'id']);
	}
}
