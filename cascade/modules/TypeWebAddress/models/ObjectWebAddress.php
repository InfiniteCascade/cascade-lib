<?php
namespace cascade\modules\TypeWebAddress\models;

use cascade\models\Registry;

/**
 * This is the model class for table "object_web_address".
 *
 * @property string $id
 * @property string $title
 * @property string $url
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
 */
class ObjectWebAddress extends \cascade\components\types\ActiveRecord
{
	public $descriptorField = 'title';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_web_address';
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
			[['url'], 'required'],
			[['id'], 'string', 'max' => 36],
			[['title'], 'string', 'max' => 255],
			[['url'], 'string', 'max' => 500]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function fieldSettings()
	{
		return [
			'title' => [],
			'url' => []
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
			'title' => 'Title',
			'url' => 'URL',
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
