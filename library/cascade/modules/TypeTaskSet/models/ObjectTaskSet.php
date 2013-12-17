<?php
namespace cascade\modules\TypeTaskSet\models;

use cascade\models\Registry;

/**
 * This is the model class for table "object_task_set".
 *
 * @property string $id
 * @property string $name
 * @property string $created
 * @property string $modified
 *
 * @property ObjectTask[] $objectTasks
 * @property Registry $registry
 */
class ObjectTaskSet extends \cascade\components\types\ActiveRecord
{
	public $descriptorField = 'name';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_task_set';
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
			[['name'], 'required'],
			[['id'], 'string', 'max' => 36],
			[['name'], 'string', 'max' => 255]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function fieldSettings()
	{
		return [
			'name' => []
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
			'name' => 'Name',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getObjectTasks()
	{
		return $this->hasMany(ObjectTask::className(), ['object_task_set_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getRegistry()
	{
		return $this->hasOne(Registry::className(), ['id' => 'id']);
	}
}
