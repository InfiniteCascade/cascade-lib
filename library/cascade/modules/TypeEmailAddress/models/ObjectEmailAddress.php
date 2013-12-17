<?php
namespace cascade\modules\TypeEmailAddress\models;

use cascade\models\Registry;
use infinite\helpers\Html;

/**
 * This is the model class for table "object_email_address".
 *
 * @property string $id
 * @property string $email_address
 * @property boolean $no_mailings
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
 */
class ObjectEmailAddress extends \cascade\components\types\ActiveRecord
{
	public $descriptorField = 'email_address';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_email_address';
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
			[['email_address'], 'required'],
			[['email_address'], 'email'],
			[['no_mailings'], 'boolean'],
			[['id'], 'string', 'max' => 36],
			[['email_address'], 'string', 'max' => 255]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function fieldSettings()
	{
		return [
			'email_address' => [],
			'no_mailings' => []
		];
	}


	/**
	 * @inheritdoc
	 */
	public function formSettings($name, $settings = [])
	{
		$settings = parent::formSettings($name, $settings);
		if (!array_key_exists('title', $settings)) {
			$settings['title'] = false;
		}
		$settings['fields'] = array();
		$settings['fields'][] = ['email_address'];
		if (!$this->isNewRecord) {
			$settings['fields'][] = ['no_mailings'];
		}
		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'email_address' => 'Email Address',
			'no_mailings' => 'No Mailings',
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

	public function getMailLink() {
		return Html::mailto($this->email_address, $this->email_address);
	}
}
