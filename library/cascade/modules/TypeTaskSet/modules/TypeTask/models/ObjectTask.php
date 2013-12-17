<?php
namespace cascade\modules\TypeTaskSet\modules\TypeTask\models;

use cascade\models\Registry;

/**
 * This is the model class for table "object_task".
 *
 * @property string $id
 * @property string $object_task_set_id
 * @property string $description
 * @property string $start
 * @property string $end
 * @property boolean $completed
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
 * @property ObjectTaskSet $objectTaskSet
 */
class ObjectTask extends \cascade\components\types\ActiveRecord
{
	public $descriptorField = 'description';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_task';
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
			[['object_task_set_id', 'description'], 'required'],
			[['description'], 'string'],
			[['start', 'end'], 'safe'],
			[['completed'], 'boolean'],
			[['id', 'object_task_set_id'], 'string', 'max' => 36]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function fieldSettings()
	{
		return [
			'description' => [],
			'start' => [],
			'end' => [],
			'completed' => []
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
			'object_task_set_id' => 'Object Task Set ID',
			'description' => 'Description',
			'start' => 'Start',
			'end' => 'End',
			'completed' => 'Completed',
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

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getObjectTaskSet()
	{
		return $this->hasOne(ObjectTaskSet::className(), ['id' => 'object_task_set_id']);
	}
}
