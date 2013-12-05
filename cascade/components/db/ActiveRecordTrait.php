<?php
/**
 * library/db/ActiveRecord.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package infinite
 */


namespace cascade\components\db;

use Yii;

use cascade\components\web\form\Segment as FormSegment;

trait ActiveRecordTrait {
	public $modelFieldClass = 'cascade\components\db\fields\Model';
	public $relationFieldClass = 'cascade\components\db\fields\Relation';
	public $formSegmentClass = 'cascade\components\web\form\Segment';
	public $_moduleHandler;

	protected $_fields;
	protected $_defaultOrder = '{alias}.name ASC';


    public function getTabularId() {
    	if (is_null($this->_moduleHandler)) {
    		$this->_moduleHandler = self::FORM_PRIMARY_MODEL;
    	}
        return self::generateTabularId($this->_moduleHandler);
    }

    public function getTabularPrefix() {
        return '['. $this->tabularId .']';
    }

	public function behaviors() {
		$behaviors = parent::behaviors();
		return array_merge($behaviors, [
			'Access' => [
				'class' => '\infinite\db\behaviors\Access',
			],
			'SearchTerm' => [
				'class' => '\infinite\db\behaviors\SearchTerm',
			]
		]);
	}


	public function getDefaultOrder($alias = 't') {
		if (is_string($this->_defaultOrder)) {
			return strtr($this->_defaultOrder, array('{alias}' => $alias));
		} else {
			return $this->_defaultOrder;
		}
	}

	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $settings (optional)
	 * @return unknown
	 */
	public function form($settings = []) {
		Yii::beginProfile(__CLASS__ .':'. __FUNCTION__);
		$settings['class'] = $this->formSegmentClass;
		$settings['model'] = $this;
		if (!isset($settings['settings'])) {
			$settings['settings'] = [];
		}
		$form = Yii::createObject($settings);
		// $form = new FormSegment($this, $name, $settings);
		Yii::endProfile(__CLASS__ .':'. __FUNCTION__);
		return $form;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function additionalFields() {
		return [
			'_moduleHandler' => []
		];
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getAdditionalAttributes() {
		$add = array();
		$af = $this->additionalFields();
		foreach (array_keys($af) as $field) {
			$add[$field] = $this->{$field};
		}
		return $add;
	}


	/**
	 *
	 *
	 * @param unknown $model                 (optional)
	 * @param unknown $univeralFieldSettings (optional)
	 * @return unknown
	 */
	public function getFields($owner = null) {
		if (is_null($this->_fields)) {
			$modelName = self::className();
			$this->_fields = [];
			$fieldSettings = $this->fieldSettings();
			foreach (array_merge($this->additionalFields(), self::getTableSchema()->columns) as  $name => $column) {
				$settings = [];
				if (isset($fieldSettings[$name])) {
					$settings = array_merge_recursive($settings, $fieldSettings[$name]);
				}
				$settings['class'] = $this->modelFieldClass;
				$settings['model'] = $this;
				$settings['field'] = $name;
				if (!isset($settings['formField'])) { $settings['formField'] = []; }
				$settings['formField']['owner'] = $owner;

				$this->_fields[$name] = Yii::createObject($settings);
			}
		}
		$objectTypeItem = $this->objectTypeItem;
		if ($objectTypeItem) {
			foreach ($objectTypeItem->parents as $relationship) {
				$fieldName = 'parent:'. $relationship->parent->systemId;
				$settings = [];
				if (isset($fieldSettings[$fieldName])) {
					$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
				}
				$settings['class'] = $this->relationFieldClass;
				$settings['model'] = $this;
				$settings['field'] = $fieldName;
				if (!isset($settings['formField'])) { $settings['formField'] = []; }
				$settings['formField']['owner'] = $owner;
				$settings['relationship'] = $relationship;
				$settings['modelRole'] = 'child';

				$this->_fields[$fieldName] = Yii::createObject($settings);
			}
			foreach ($objectTypeItem->children as $relationship) {
				$fieldName = 'child:'. $relationship->child->systemId;
				$settings = [];
				if (isset($fieldSettings[$fieldName])) {
					$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
				}
				$settings['class'] = $this->relationFieldClass;
				$settings['model'] = $this;
				$settings['field'] = $fieldName;
				if (!isset($settings['formField'])) { $settings['formField'] = []; }
				$settings['formField']['owner'] = $owner;
				$settings['relationship'] = $relationship;
				$settings['modelRole'] = 'parent';

				$this->_fields[$fieldName] = Yii::createObject($settings);
			}
		}
		return $this->_fields;
	}

	public function getObjectType() {
		$objectTypeItem = $this->objectTypeItem;
		if ($objectTypeItem) {
			return $objectTypeItem->object;
		}
		return false;
	}

	public function getObjectTypeItem() {
		if (Yii::$app->collectors['types']->has(get_class($this), 'object.primaryModel')) {
			return Yii::$app->collectors['types']->getOne(get_class($this), 'object.primaryModel');
		}
		return false;
	}
	/**
	 *
	 *
	 * @return unknown
	 */
	public function fieldSettings() {
		return null;
	}


	public function formSettings($name, $settings = [])
	{
		if (!is_array($settings)) {
			$settings = [];
		}
		return $settings;
	}

	/**
	 *
	 *
	 * @param unknown $key (optional)
	 * @return unknown
	 */
	public function setFormValues($key = null) {
		if (!isset($_POST[get_class($this)])) { return true; }
		$base = $_POST[get_class($this)];
		if (is_null($key) or $key === 'primary') {
			if (!empty($base)) {
				$this->attributes = $base;
			}
		} else {
			$key = md5($key);
			if (!empty($base[$key])) {
				$this->attributes = $base[$key];
			}
		}

		return $this->validate();
	}
}


?>
