<?php
/**
 * library/db/ActiveRecord.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package infinite
 */


namespace cascade\components\types;

use Yii;

use yii\helpers\Inflector;

use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;

use cascade\components\web\form\Segment as FormSegment;
use cascade\components\types\Relationship;
use cascade\components\db\fields\Base as BaseField;
use cascade\components\db\behaviors\SearchTerm;
use cascade\models\Relation;

trait ActiveRecordTrait {
	use SearchTerm;

	public $baseFieldClass = 'cascade\\components\\db\\fields\\Base';
	public $artificialFieldClass = 'cascade\\components\\db\\fields\\Artificial';
	public $modelFieldClass = 'cascade\\components\\db\\fields\\Model';
	public $relationFieldClass = 'cascade\\components\\db\\fields\\Relation';
	public $taxonomyFieldClass = 'cascade\\components\\db\\fields\\Taxonomy';
	public $formSegmentClass = 'cascade\\components\\web\\form\\Segment';
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
			],
			'Relatable' => [
				'class' => 'infinite\\db\\behaviors\\Relatable',
			],
			'Taxonomy' => [
				'class' => 'cascade\\components\\db\\behaviors\\Taxonomy',
			],
			'ActiveAccess' => [
				'class' => 'infinite\\db\\behaviors\\ActiveAccess',
			],
			'Roleable' => [
				'class' => 'cascade\\components\\db\\behaviors\\Roleable',
			],
			'Ownable' => [
				'class' => 'cascade\\components\\db\\behaviors\\Ownable',
			],
			'Familiarity' => [
				'class' => 'cascade\\components\\db\\behaviors\\Familiarity',
			],
		];
	}


    public function isForeignField($field)
    {
        return parent::isForeignField($field) && strpos($field, ':') !== false;
    }
	

    public static function searchForeign($term, $field)
	{
		$modelClass = get_called_class();
		$model = new $modelClass;

		$package = [];
		$parts = explode(':', $field);
    	if (count($parts) !== 2) { return $package; }
    	$relationshipType = $parts[0];
    	if (!in_array($relationshipType, ['child', 'children', 'descendants', 'parent', 'parents', 'ancestors'])) {
    		return null;
    	}

		$searchFields = array_unique(static::searchFields());
    	$companionName = $parts[1];
    	$fieldName = isset($parts[2]) ? $parts[2] : 'descriptor';
    	$myTypeItem = $model->objectTypeItem;
    	$companionTypeItem = Yii::$app->collectors['types']->getOne($companionName);
    	if (!$companionTypeItem || !$myTypeItem || !($companionType = $companionTypeItem->object) || !($myType = $myTypeItem->object)) { return null; }
    	if (in_array($relationshipType, ['child', 'children', 'descendants'])) {
    		// I'm the parent
    		$relationship = Relationship::has($myTypeItem, $companionTypeItem) ? Relationship::getOne($myTypeItem, $companionTypeItem) : false;
    		$seek = 'parents';
    	} else {
    		$relationship = Relationship::has($companionTypeItem, $myTypeItem) ? Relationship::getOne($companionTypeItem, $myTypeItem) : false;
    		$seek = 'children';
    	}
    	if (!$relationship) { return $package; }
    	$results = $companionType->search($term);
    	foreach ($results as $result) {
    		if (empty($result->object)) { continue; }
    		foreach ($result->object->$seek($myType->primaryModel) as $object) {
    			$package[$object->id] = self::createSearchResult($object, $searchFields, $result->score);
    			$package[$object->id]->mergeTerms([$result->object->descriptor]);
    			$package[$object->id]->addSubdescriptorValue($result->object->descriptor);
    		}
    	}

		return $package;
	}

    public function getForeignField($field, $relationOptions = [], $objectOptions = [])
    {
    	$parts = explode(':', $field);
    	if (!in_array(count($parts), [2, 3])) { return null; }
    	$relationshipType = $parts[0];
    	if (!in_array($relationshipType, ['child', 'children', 'descendants', 'parent', 'parents', 'ancestors'])) {
    		return null;
    	}
    	$companionName = $parts[1];
    	$fieldName = isset($parts[2]) ? $parts[2] : 'descriptor';
    	$myTypeItem = $this->objectTypeItem;
    	$companionTypeItem = Yii::$app->collectors['types']->getOne($companionName);
    	if (!$companionTypeItem || !$myTypeItem || !($companionType = $companionTypeItem->object) || !($myType = $myTypeItem->object)) { return null; }
    	if (in_array($relationshipType, ['child', 'children', 'descendants'])) {
    		// I'm the parent
    		$relationship = Relationship::has($myTypeItem, $companionTypeItem) ? Relationship::getOne($myTypeItem, $companionTypeItem) : false;
    	} else {
    		$relationship = Relationship::has($companionTypeItem, $myTypeItem) ? Relationship::getOne($companionTypeItem, $myTypeItem) : false;
    	}
    	if (!$relationship) { return null; }
    	$result = $this->{$relationshipType}($companionType->primaryModel, $relationOptions, $objectOptions);
    	if (empty($result)) {
    		return null;
    	}
    	if (is_array($result)) {
    		$fields = [];
    		foreach ($result as $object) {
    			$field = $object->getField($fieldName);
    			if (empty($field)) { continue; }
    			$companionType->loadFieldLink($field, $object);
    			$fields[] = $field;
    		}
    		return $fields;
    	} else {
    		$field = $result->getField($fieldName);
    		if (empty($field)) { continue; }
    		$companionType->loadFieldLink($field, $result);
    		return $field;
    	}
        return null; 
    }

    public function getForeignFieldValue($field)
    {
    	$field = $this->getForeignField($field);
    	if (!empty($field)) {
    		if (is_array($field)) {
    			$rich = [];
	    		$plain = [];
	    		foreach ($field as $object) {
	    			$valuePackage = $object->valuePackage;
	    			if (!empty($valuePackage) && !empty($valuePackage['rich']) && !empty($valuePackage['plain'])) {
	    				$rich[] = $valuePackage['rich'];
	    				$plain[] = $valuePackage['plain'];
	    			}
	    		}
	    		return ['rich' => implode(', ', $rich), 'plain' => implode(', ', $plain)];
    		} else {
    			return $field->valuePackage;
    		}
    	}
    	return null;
    }


	public static function searchFields()
	{
		$modelClass = get_called_class();
		$model = new $modelClass;

		if (is_null($model->descriptorField)) {
			$fields = [];
		} elseif(is_array($model->descriptorField)) {
			$fields = $model->descriptorField;
		} else {
			$fields = [$model->descriptorField];
		}

		$fields = array_intersect($fields, $model->attributes());
		if (($moduleItem = $model->getObjectTypeItem())) {
			foreach ($moduleItem->children as $key => $relationship) {
				if ($relationship->child->searchForParent) {
					$fields[] = 'child:'.$key;
				}
			}
		}
		return $fields;
	}

	public static function parseSearchFields($fields)
	{
		$localFields = [];
		$foreignTypes = [];
		foreach ($fields as $field) {
			if (strpos($field, ':') === false) {
				$localFields[] = $field;
			} else {
				$foreignTypes[] = $field;
			}
		}
		return [$localFields, $foreignTypes];
	}

	public function getDefaultValues()
	{
		return [];
	}

	public function loadDefaultValues($skipIfSet = true)
	{
		parent::loadDefaultValues($skipIfSet);
		$defaultValues = $this->getDefaultValues();
		foreach ($defaultValues as $k => $v) {
			if ($skipIfSet && !$this->isAttributeChanged($k)) {
				$this->{$k} = $v;
			}
		}
	}

	public function getUrl($action = 'view', $base = [], $pathLink = true) {
		$url = ['/object/'. $action, 'id' => $this->primaryKey];
		$url = array_merge($url, $base);
		if ($action === 'view' 
				&& $pathLink === true 
				&& isset(Yii::$app->request->object)
				&& Yii::$app->request->object->primaryKey !== $this->primaryKey) {
			$url['p'] = Yii::$app->request->object->primaryKey;
		}
		return $url;
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
		if ($this->getBehavior('Relatable') !== null && count($this->parentIds) > 1) {
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

	public function getFieldLocation($location, $owner = null, $allFields = null)
	{
		if (is_null($allFields)) {
			$allFields = $this->getFields($owner);
		}
		$fields = [];
		foreach ($allFields as $key => $field) {
			if (in_array($location, $field->locations)) {
				$fields[] = $field;
			}
		}
		return $fields;
	}

	public function getDetailFields($owner = null, $fields = null)
	{
		return $this->getFieldLocation(BaseField::LOCATION_DETAILS, $owner, $fields);
	}

	public function getHiddenFields($owner = null, $fields = null)
	{
		return $this->getFieldLocation(BaseField::LOCATION_HIDDEN, $owner, $fields);
	}

	public function getHeaderFields($owner = null, $fields = null)
	{
		return $this->getFieldLocation(BaseField::LOCATION_HEADER, $owner, $fields);
	}

	public function getSubheaderFields($owner = null, $fields = null)
	{
		return $this->getFieldLocation(BaseField::LOCATION_SUBHEADER, $owner, $fields);
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
		
		if (is_object($this->objectType) && ($disabledFields = $this->objectType->disabledFields) && !empty($disabledFields)) {
			foreach ($validators as $validator) {
				$validator->attributes = array_diff($validator->attributes, $disabledFields);
			}
		}

		return $validators;
	}

	public function getField($field, $owner = null)
	{
		$fields = $this->fields;
		if (isset($fields[$field])) {
			return $fields[$field];
		} else {
			$functionName = 'get'. Inflector::camelize($field) .'Field';
			if (method_exists($this, $functionName)) {
				return $this->$functionName();
			} elseif (isset($this->{$field})) {
				// create artificial field
				return $this->createArtificialField($field, $this->{$field}, $owner);
			}
		}
		return null;
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
				$relationClass = Yii::$app->classes['Relation'];
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
		if (!is_object($fieldSchema)) {
			$fieldSchema = $this->createColumnSchema($fieldSchema);
		}
		$settings['fieldSchema'] = $fieldSchema;
		$settings['required'] = $this->isAttributeRequired($fieldSchema->name);

		if (!isset($settings['formField'])) { $settings['formField'] = []; }
		$settings['formField']['owner'] = $owner;

		return Yii::createObject($settings);
	}

	public function createArtificialField($fieldName, $fieldValue, $owner, $settings = [])
	{
		$settings['class'] = $this->artificialFieldClass;
		if (!isset($settings['model'])) {
			$settings['model'] = $this;
		}
		$settings['fieldName'] = $fieldName;
		$settings['fieldValue'] = $fieldValue;
		$settings['required'] = false;
		$settings['formField'] = false;;
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
		if (isset(Yii::$app->collectors['types']) && Yii::$app->collectors['types']->has(get_class($this), 'object.primaryModel')) {
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
