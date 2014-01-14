<?php
/**
 * library/db/ActiveRecord.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package infinite
 */


namespace cascade\components\types;

use Yii;

use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;

use cascade\components\web\form\Segment as FormSegment;
use cascade\components\types\Relationship;
use cascade\models\Relation;

trait ActiveRecordTrait {
	public $baseFieldClass = 'cascade\components\db\fields\Base';
	public $modelFieldClass = 'cascade\components\db\fields\Model';
	public $relationFieldClass = 'cascade\components\db\fields\Relation';
	public $relationClass = 'cascade\models\Relation';
	public $taxonomyFieldClass = 'cascade\components\db\fields\Taxonomy';
	public $formSegmentClass = 'cascade\components\web\form\Segment';
	public $columnSchemaClass = 'yii\\db\\ColumnSchema';

	public $defaultCustomColumnSchema = [
		'allowNull' => false,
		'type' => 'string',
		'phpType' => 'string',
		'dbType' => 'string',
		'isPrimaryKey' => false
	];

	public function behaviors() {
		return [
			'Registry' => [
				'class' => 'infinite\\db\\behaviors\\Registry',
				'registryClass' => 'cascade\\models\\Registry',
			],
			'Relatable' => [
				'class' => 'infinite\\db\\behaviors\\Relatable',
				'relationClass' => 'cascade\\models\\Relation',
				'registryClass' => 'cascade\\models\\Registry',
			],
			'Taxonomy' => [
				'class' => 'cascade\\components\\db\\behaviors\\Taxonomy',
			],
			'Access' => [
				'class' => 'infinite\\db\\behaviors\\Access',
			]
		];
	}

	public function getDefaultValues()
	{
		return [];
	}

	public function loadDefaultValues() {
		$defaultValues = $this->getDefaultValues();
		foreach ($defaultValues as $k => $v) {
			if (!$this->isAttributeChanged($k)) {
				$this->{$k} = $v;
			}
		}
	}

	public function getUrl($action = 'view') {
		return ['object/'. $action, 'id' => $this->primaryKey];
	}

	public function getViewLink()
	{
		return Html::a($this->descriptor, $this->getUrl('view'));
	}

	public function allowRogue(Relation $relation = null)
	{
		$relationship = false;
		if (is_null($relation)) {
			$relation = false;
		} else {
			$relationship = $relation->relationship;
		}

		if ($relationship && $relationship->isHasOne()) {
			return false;
		}
		if ($relation && isset($relation->childObject) && isset($relation->childObject->objectType) && $relation->childObject->objectType->uniparental) {
			return false;
		}
		if ($this->objectType->hasDashboard) {
			return true;
		}
		if (count($this->parentIds) > 1) {
			return true;
		}
		return false;
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


	public function getDefaultOrder($alias = 't')
	{
		if (is_string($this->_defaultOrder)) {
			return strtr($this->_defaultOrder, ['{alias}' => $alias]);
		} else {
			return $this->_defaultOrder;
		}
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
		$add = [];
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
			if (!$field->human) { continue; }
			if ($field->required) {
				$required[$key] = $field;
			}
		}
		return $required;
	}

	public function getHeaderDetails()
	{
		return [];
	}

	public function getDetailFields($owner = null, $fields = null)
	{
		if (is_null($fields)) {
			$fields = $this->getFields($owner);
		}
		foreach ($fields as $key => $field) {
			$keep = true;
			if ($field instanceof $this->relationFieldClass) {
				if (	(in_array($key, $this->headerDetails))
					|| 	($field->modelRole === 'child' && !$field->relationship->isHasOne())
					||	($field->modelRole === 'parent')) {
					$keep = false;
				}
			} elseif (!$field->human) {
				$keep = false;
			}

			if (!$keep) {
				unset($fields[$key]);
			}
		}
		return $fields;
	}

	public function createColumnSchema($name, $settings = [])
	{
		$settings = array_merge($this->defaultCustomColumnSchema, $settings);
		$settings['name'] = $name;
		$settings['class'] = $this->columnSchemaClass;
		return Yii::createObject($settings);
	}

	public function getValidators()
	{
		$validators = parent::getValidators();
		
		if (isset($this->objectType) && ($disabledFields = $this->objectType->disabledFields) && !empty($disabledFields)) {
			foreach ($validators as $validator) {
				$validator->attributes = array_diff($validator->attributes, $disabledFields);
			}
		}

		return $validators;
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
			$disabledFields = $this->objectType->disabledFields;
			$modelName = self::className();
			self::$_fields[self::className()] = [];
			$fieldSettings = $this->fieldSettings();
			foreach (array_merge($this->additionalFields(), self::getTableSchema()->columns) as  $name => $column) {
				if (in_array($name, $disabledFields)) { continue; }
				$settings = [];
				if (isset($fieldSettings[$name])) {
					$settings = array_merge_recursive($settings, $fieldSettings[$name]);
				}
				if (is_array($column)) {
					$column = $this->createColumnSchema($name, $column);
				}
				self::$_fields[self::className()][$name] = $this->createField($column, $owner, $settings);
			}
			$objectTypeItem = $this->objectTypeItem;
			if ($objectTypeItem) {
				$relationRelationship = null;
				$relationClass = $this->relationClass;
				$taxonomies = $objectTypeItem->taxonomies;
				foreach ($objectTypeItem->parents as $relationship) {
					$fieldName = 'parent:'. $relationship->parent->systemId;
					if (in_array($fieldName, $disabledFields)) { continue; }
					$fieldSchema = $this->createColumnSchema($fieldName, ['type' => 'relation', 'phpType' => 'object', 'dbType' => 'relation', 'allowNull' => true]);
					$settings = [];
					if (isset($fieldSettings[$fieldName])) {
						$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
					}
					$settings['modelRole'] = 'child';
					$settings['relationship'] = $relationship;
					self::$_fields[self::className()][$fieldName] = $this->createRelationField($fieldSchema, $owner, $settings);
				}

				foreach ($objectTypeItem->children as $relationship) {
					$fieldName = 'child:'. $relationship->child->systemId;
					if (in_array($fieldName, $disabledFields)) { continue; }
					$fieldSchema = $this->createColumnSchema($fieldName, ['type' => 'relation', 'phpType' => 'object', 'dbType' => 'relation', 'allowNull' => true]);
					$settings = [];
					if (isset($fieldSettings[$fieldName])) {
						$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
					}
					$settings['modelRole'] = 'parent';
					$settings['relationship'] = $relationship;
					self::$_fields[self::className()][$fieldName] = $this->createRelationField($fieldSchema, $owner, $settings);
				}

				foreach ($taxonomies as $taxonomy) {
					if(!in_array(self::className(), $taxonomy->models)) {
						continue;
					}

					$fieldName = 'taxonomy:'. $taxonomy->systemId;
					if (in_array($fieldName, $disabledFields)) { continue; }
					$fieldSchema = $this->createColumnSchema($fieldName, ['type' => 'taxonomy', 'phpType' => 'object', 'dbType' => 'taxonomy', 'allowNull' => true]);

					$settings = [];
					if (isset($fieldSettings[$fieldName])) {
						$settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
					}
					$settings['model'] = $this;
					self::$_fields[self::className()][$fieldName] = $this->createTaxonomyField($fieldSchema, $taxonomy, $owner);
				}
			}
			$currentKeys = array_keys(self::$_fields[self::className()]);
			foreach (self::$_fields[self::className()] as $name => $field) {
				if (!isset($field->priority)) {
					$field->priority = (int) array_search($name, $currentKeys);
					$field->priority = ($field->priority * 100);
				}
			}
			// \d(ArrayHelper::getColumn(self::$_fields[self::className()], 'priority'));
			ArrayHelper::multisort(self::$_fields[self::className()], 'priority', SORT_ASC);
		}
		return self::$_fields[self::className()];
	}

	public function createField($fieldSchema, $owner, $settings = [])
	{
		$settings['class'] = $this->modelFieldClass;
		if (!isset($settings['model'])) {
			$settings['model'] = $this;
		}
		$settings['fieldSchema'] = $fieldSchema;
		$settings['required'] = $this->isAttributeRequired($fieldSchema->name);

		if (!isset($settings['formField'])) { $settings['formField'] = []; }
		$settings['formField']['owner'] = $owner;

		return Yii::createObject($settings);
	}


	public function createRelationField($fieldSchema, $owner, $settings = [])
	{
		$settings['class'] = $this->relationFieldClass;
		if (!isset($settings['baseModel'])) {
			$settings['baseModel'] = $this;
		}
		$settings['fieldSchema'] = $fieldSchema;
		$settings['required'] = $this->isAttributeRequired($fieldSchema->name);
		$settings['baseModel'] = $this;

		if (!isset($settings['formField'])) { $settings['formField'] = []; }
		$settings['formField']['owner'] = $owner;

		return Yii::createObject($settings);
	}

	public function createTaxonomyField($fieldSchema, $taxonomy, $owner, $settings = []) {
		$settings['class'] = $this->taxonomyFieldClass;
		$settings['fieldSchema'] = $fieldSchema;
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
