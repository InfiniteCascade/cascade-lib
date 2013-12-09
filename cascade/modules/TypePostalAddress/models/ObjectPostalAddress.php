<?php
namespace cascade\modules\TypePostalAddress\models;

use Yii;

use cascade\models\Registry;
use infinite\helpers\Locations;

/**
 * This is the model class for table "object_postal_address".
 *
 * @property string $id
 * @property string $name
 * @property string $type
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $subnational_division
 * @property string $postal_code
 * @property string $country
 * @property boolean $no_mailings
 * @property string $created
 * @property string $modified
 *
 * @property Registry $registry
 */
class ObjectPostalAddress extends \cascade\components\types\ActiveRecord
{
	public $descriptorField = 'name';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_postal_address';
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
			[['type'], 'string'],
			[['no_mailings'], 'boolean'],
			[['id'], 'string', 'max' => 36],
			[['city'], 'required'],
			[['name', 'address1', 'address2', 'city', 'country'], 'string', 'max' => 255],
			[['subnational_division'], 'string', 'max' => 100],
			[['postal_code'], 'string', 'max' => 20]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function fieldSettings()
	{
		return [
			'name' => [],
			'type' => [],
			'address1' => [],
			'address2' => [],
			'city' => [],
			'subnational_division' => [
				'default' => Yii::$app->params['defaultSubnationalDivision'],
				'formField' => array('type' => 'smartDropDownList', 'smartOptions' => ['watchField' => 'country', 'fallbackType' => ['tag' => 'input', 'type' => 'text'], 'options' => Locations::allSubnationalDivisions(), 'blank' => true], 'options' => []),
			],
			'postal_code' => [],
			'country' => [
				'default' => Yii::$app->params['defaultCountry'],
				'formField' => array('type' => 'dropDownList', 'options' => Locations::countryList()),
			],
			'no_mailings' => []
		];
	}


	/**
	 * @inheritdoc
	 */
	public function formSettings($name, $settings = [])
	{
		if (!array_key_exists('title', $settings)) {
			$settings['title'] = false;
		}
		$settings['fields'] = array();
		$settings['fields'][] = ['relation:taxonomy_id', 'name' => ['columns' => 8]];
		$settings['fields'][] = ['address1', 'address2'];
		$settings['fields'][] = ['city', 'subnational_division', 'postal_code'];
		$settings['fields'][] = ['country', false, false];
		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'Name',
			'type' => 'Type',
			'address1' => 'Address (Line 1)',
			'address2' => 'Address (Line 2)',
			'city' => 'City',
			'subnational_division' => 'State/Province/Region',
			'postal_code' => 'Postal Code',
			'country' => 'Country',
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


	public function getUniqueCountry() {
		if ($this->country !==Yii::$app->params['defaultCountry']) {
			$countries = Locations::countryList();
			return $countries[$this->country];
		}
		return null;
	}


	public function getCsz() {
		$str = $this->city;
		if (!empty($this->subnational_division)) {
			$str .= ", ". $this->subnational_division;
		}
		if (!empty($this->postal_code)) {
			$str .= " ". $this->postal_code;
		}
		return $str;
	}

	public function getFlatAddressUrl()
	{
		return urlencode($this->flatAddress);
	}
	
	public function getFlatAddress()
	{
		$parts = ['address1', 'address2', 'csz', 'country'];
		$address = [];
		foreach ($parts as $part) {
			if (isset($this->{$part})) {
				$address[] = $this->{$part};
			}
		}
		return implode(', ', $address);
	}
}
