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
	public $relationClass = 'cascade\models\Relation';
	public $taxonomyFieldClass = 'cascade\components\db\fields\Taxonomy';
	public $formSegmentClass = 'cascade\components\web\form\Segment';
	public $_moduleHandler;

	static protected $_fields = [];
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

	public function getRequiredFields($owner = null)
	{
		$fields = $this->getFields($owner);
		$required = [];
		foreach ($fields as $key => $field) {
			if ($field->required) {
				$required[$key] = $field;
			}
		}
		return $required;
	}


	/**
	 *
	 *
	 * @param unknown $model                 (optional)
	 * @param unknown $univeralFieldSettings (optional)
	 * @return unknown
	 */
	public function getFields($owner = null) {
		if (!isset(self::$_fields[self::className()])) {
			$modelName = self::className();
			self::$_fields[self::className()] = [];
			$fieldSettings = $this->fieldSettings();
			foreach (array_merge($this->additionalFields(), self::getTableSchema()->columns) as  $name => $column) {
				$settings = [];
				if (isset($fieldSettings[$name])) {
					$settings = array_merge_recursive($settings, $fieldSettings[$name]);
				}
				$settings['class'] = $this->modelFieldClass;
				$settings['model'] = $this;
				$settings['field'] = $name;
				$settings['required'] = $this->isAttributeRequired($name);

				if (!isset($settings['formField'])) { $settings['formField'] = []; }
				$settings['formField']['owner'] = $owner;

				self::$_fields[self::className()][$name] = Yii::createObject($settings);
			}
			$objectTypeItem = $this->objectTypeItem;
			if ($objectTypeItem) {
				$relationClass = $this->relationClass;
				$taxonomies = $objectTypeItem->taxonomies;
				foreach ($objectTypeItem->parents as $relationship) {
					$fieldName = 'parent:'. $relationship->parent->systemId;
					$settings = [];
					if (isset($fieldSettings[$fieldName])) {
						$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
					}
					$settings['class'] = $this->relationFieldClass;
					$settings['model'] = $this->getRelationModel($fieldName);
					$settings['field'] = $fieldName;
					if (!isset($settings['formField'])) { $settings['formField'] = []; }
					$settings['formField']['owner'] = $owner;
					$settings['relationship'] = $relationship;
					$settings['modelRole'] = 'child';
					self::$_fields[self::className()][$fieldName] = Yii::createObject($settings);

					if (isset($relationship->taxonomy) 
						&& isset($taxonomies[$relationship->taxonomy])
						&& in_array($relationClass::className(), $taxonomies[$relationship->taxonomy]->models)
					) {
						$taxonomy = $taxonomies[$relationship->taxonomy];
						unset($taxonomies[$relationship->taxonomy]);
						$fieldName = 'parentTaxonomy:'. $taxonomy->systemId;
						$settings = ['model' => $settings['model']];
						self::$_fields[self::className()][$fieldName] = $this->_createTaxonomyField($fieldName, $taxonomy, $owner, $settings);
					}
				}
				foreach ($objectTypeItem->children as $relationship) {
					$fieldName = 'child:'. $relationship->child->systemId;
					$settings = [];
					if (isset($fieldSettings[$fieldName])) {
						$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
					}
					$settings['class'] = $this->relationFieldClass;
					$settings['model'] = $this->getRelationModel($fieldName);
					$settings['field'] = $fieldName;
					if (!isset($settings['formField'])) { $settings['formField'] = []; }
					$settings['formField']['owner'] = $owner;
					$settings['relationship'] = $relationship;
					$settings['modelRole'] = 'parent';
					self::$_fields[self::className()][$fieldName] = Yii::createObject($settings);

					if (isset($relationship->taxonomy) 
						&& isset($taxonomies[$relationship->taxonomy])
						&& in_array($relationClass::className(), $taxonomies[$relationship->taxonomy]->models)
					) {
						$taxonomy = $taxonomies[$relationship->taxonomy];
						unset($taxonomies[$relationship->taxonomy]);
						$fieldName = 'childTaxonomy:'. $taxonomy->systemId;
						$settings = ['model' => $settings['model']];
						self::$_fields[self::className()][$fieldName] = $this->_createTaxonomyField($fieldName, $taxonomy, $owner, $settings);
					}
				}
				foreach ($taxonomies as $taxonomy) {
					$fieldName = 'taxonomy:'. $taxonomy->systemId;
					$settings = [];
					if (isset($fieldSettings[$fieldName])) {
						$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
					}
					$settings['model'] = $this;
					self::$_fields[self::className()][$fieldName] = $this->_createTaxonomyField($fieldName, $taxonomy, $owner);
				}
			}
		}
		return self::$_fields[self::className()];
	}

	protected function _createTaxonomyField($fieldName, $taxonomy, $owner, $settings = []) {
		$settings['class'] = $this->taxonomyFieldClass;
		$settings['field'] = 'taxonomy_id';
		if (!isset($settings['formField'])) { $settings['formField'] = []; }
		$settings['formField']['owner'] = $owner;
		$settings['taxonomy'] = $taxonomy;
		$settings['required'] = $taxonomy->required;
		return Yii::createObject($settings);
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
