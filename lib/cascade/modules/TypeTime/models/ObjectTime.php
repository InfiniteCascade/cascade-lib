<?php
namespace cascade\modules\TypeTime\models;

use cascade\models\Registry;

/**
 * This is the model class for table "object_time".
 *
 * @property string $id
 * @property string $description
 * @property string $hours
 * @property string $log_date
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
 */
class ObjectTime extends \cascade\components\types\ActiveRecord
{
	public $descriptorField = 'name';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_time';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), []);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['description'], 'string'],
			[['hours'], 'number'],
			[['log_date'], 'safe'],
			[['id'], 'string', 'max' => 36]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function fieldSettings()
	{
		return [
			'description' => [],
			'hours' => [],
			'log_date' => []
		];
	}


	/**
	 * @inheritdoc
	 */
	public function formSettings($name, $settings = [])
	{
		return parent::formSettings($name, $settings);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'description' => 'Description',
			'hours' => 'Hours',
			'log_date' => 'Log Date',
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
