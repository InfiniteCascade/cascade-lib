<?php
namespace cascade\components\dataInterface\connectors\db;

use Yii;

use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\KeyTranslation;
use cascade\components\dataInterface\Action;
use cascade\components\dataInterface\DataItem as BaseDataItem;

use infinite\helpers\ArrayHelper;

class DataSource extends \cascade\components\dataInterface\DataSource {
	public $fieldMapClass = 'cascade\\components\\dataInterface\\connectors\\db\\FieldMap';
	public $dataItemClass = 'cascade\\components\\dataInterface\\connectors\\db\\DataItem';
	public $searchClass = 'cascade\\components\\dataInterface\\connectors\\db\\Search';

	public function init()
	{
		$this->on(self::EVENT_LOAD_FOREIGN_DATA_ITEMS, [$this, 'loadForeignDataItems']);
		$this->on(self::EVENT_LOAD_LOCAL_DATA_ITEMS, [$this, 'loadLocalDataItems']);
		return parent::init();
	}

	public function getForeignDataItem($key)
	{
		if (!isset($this->_foreignDataItems[$key])) {
			$this->createForeignDataItem(null, ['foreignPrimaryKey' => $key]);
		}
		if (isset($this->_foreignDataItems[$key])) {
			return $this->_foreignDataItems[$key];
		}
		return false;
	}

	public function getForeignDataModel($key)
	{
		$config = $this->settings['foreignPullParams'];
		if (!isset($config['where'])) {
			$config['where'] = [];
		}
		if (!empty($config['where'])) {
			$config['where'] = ['and', $config['where'], [$this->foreignModel->primaryKey() => $key]];
		} else {
			$config['where'][$this->foreignModel->primaryKey()] = $key;
		}
		//var_dump($this->foreignModel->find($config)->count('*', $this->module->db));
		return $this->foreignModel->findOne($config);
	}

	public function buildLocalAttributes(Model $foreignModel, $localModel = null)
	{
		$a = [];
		foreach ($this->map as $localKey => $fieldMap) {
			if ($localKey === $this->localPrimaryKeyName) { continue; }
			$value = $fieldMap->extractValue($foreignModel);
			$taxonomyId = null;
			if (isset($fieldMap->taxonomy) && isset($fieldMap->taxonomy['taxonomy_type'])) {
				$taxonomyTypeItem = Yii::$app->collectors['taxonomies']->getOne($fieldMap->taxonomy['taxonomy_type']);
				if (($taxonomyType = $taxonomyTypeItem->object) && isset($taxonomyType)) {
					if (isset($fieldMap->taxonomy['taxonomy_system_id']) 
						&& ($taxonomy = $taxonomyTypeItem->getTaxonomy($fieldMap->taxonomy['taxonomy_system_id']))
						&& (isset($taxonomy))
					) {
						$taxonomyId = [$taxonomy->primaryKey];
					}
				}
			}
			
			if (strpos($fieldMap->localField, ':') !== false) {
				// we're feeding the relations
				$relationKeys = $value;
				$value = false;
				if (!empty($relationKeys)) {
					if (!is_array($relationKeys)) {
						$relationKeys = [$relationKeys];
					}
					$fieldParts = explode(':', $fieldMap->localField);
					if ($fieldParts[0] === 'child') {
						$relationship = $this->dummyLocalModel->objectTypeItem->getChild($fieldParts[1]);
						$relatedType = !empty($relationship) ? $relationship->child : false;
						$currentRelationsFunction = 'child';
					} else {
						$relationship = $this->dummyLocalModel->objectTypeItem->getParent($fieldParts[1]);
						$relatedType = !empty($relationship) ? $relationship->parent : false;
						$currentRelationsFunction = 'parent';
					}
					if (!$relatedType) { continue; }

					$relatedObject = null;
					if (!isset($a['relationModels'])) {
						$a['relationModels'] = [];
					}
					$fieldKey = $fieldParts[0] .'_object_id';
					foreach ($relationKeys as $relationKey) {
						if (empty($fieldParts[2])) {
							// we're just matching to an existing object's primary key
							if (($relatedObject = $this->module->getLocalObject($relatedType->primaryModel, $relationKey)) && is_object($relatedObject)) {
								$relation = [$fieldKey => $relatedObject->primaryKey];
								if (isset($taxonomyId)) {
									$relation['taxonomy_id'] = $taxonomyId;
									$taxonomyId = null;
								}
								$a['relationModels'][] = $relation;
							}
						} else {
							// we're creating or updating an existing related object's field
							$localRelatedField = $fieldParts[2];
							if (is_array($relationKey)) {
								// the localRelatedField is a dummy; build/search for object using this hash
								$valueMap = $relationKey;
							} else {
								$valueMap = [$localRelatedField => $relationKey];
							}
							if (($relatedObject = $this->module->updateLocalObject($relatedType, $relationKey, $valueMap, $fieldMap, $localModel)) && is_object($relatedObject)) {
								$relation = [$fieldKey => $relatedObject->primaryKey];
								if (isset($taxonomyId)) {
									$relation['taxonomy_id'] = $taxonomyId;
									$taxonomyId = null;
								}
								$a['relationModels'][] = $relation;
							}
						}
					}
				}
			} elseif (!empty($fieldMap->foreignModel)) {
				$relationKey = $value;
				$value = false;
				if (!empty($relationKey)) {
					// we're filling a local related _id field with another foreign object
					if (($relatedObject = $this->module->getForeignObject($fieldMap->foreignModel, $relationKey)) && is_object($relatedObject)) {
						$value = $relatedObject->primaryKey;
					}
				}
			}
			if ($value !== false) {
				$a[$fieldMap->localField] = $value;
			}
		}
		return $a;
	}

	public function buildLocalAttributesOld(Model $foreignModel, $localModel = null)
	{
		$a = [];
		foreach ($this->map as $localKey => $fieldMap) {
			if ($localKey === $this->localPrimaryKeyName) { continue; }
			if (strpos($fieldMap->localField, ':') !== false) {
				if (($relationKey = $fieldMap->extractValue($foreignModel)) && !empty($relationKey)) {
					$fieldParts = explode(':', $fieldMap->localField);
					if ($fieldParts[0] === 'child') {
						$relationship = $this->dummyLocalModel->objectTypeItem->getChild($fieldParts[1]);
						$relatedType = !empty($relationship) ? $relationship->child : false;
						$currentRelationsFunction = 'child';
					} else {
						$relationship = $this->dummyLocalModel->objectTypeItem->getParent($fieldParts[1]);
						$relatedType = !empty($relationship) ? $relationship->parent : false;
						$currentRelationsFunction = 'parent';
					}
					$relatedObject = null;
					if (!isset($a['relations'])) {
						$a['relations'] = [];
					}
					if (!isset($a['relations'][$fieldParts[0]])) {
						$a['relations'][$fieldParts[0]] = [];
					}
					if (empty($fieldParts[2])) {
						// we're just matching to an existing objects primary key
						if ($relatedType && ($relatedObject = $this->module->getLocalObject($relatedType->primaryModel, $relationKey)) && is_object($relatedObject)) {
							$a['relations'][$fieldParts[0]][] = $relatedObject->primaryKey;
						} elseif (!is_object($relatedObject)) {
							\d([$relatedType->primaryModel, $relationKey]);
							\d($relatedObject); exit;
						}
					} else {
						// we're creating or updating an existing related object's field
						$localRelatedField = $fieldParts[2];
						if (is_array($relationKey)) {
							// the localRelatedField is a dummy; build/search for object using this hash
							$valueMap = $relationKey;
						} else {
							$valueMap = [$localRelatedField => $relationKey];
						}

						// @todo eventually we'll probably take some keys out of this
						$searchMap = $valueMap;

						// first, lets see if it exists
						$relatedObject = null;
						$currentRelation = false;
						if (!empty($localModel) && !$localModel->isNewRecord) {
							$test = $localModel->{$currentRelationsFunction}($relatedType->primaryModel, [], ['where' => $searchMap]);
							if ($test) {
								$relatedObject = $test;
								$currentRelation = true;
							}
						}

						if (empty($relatedObject)) {
							$relatedClass = $relatedType->primaryModel;
							$relatedObject = new $relatedClass;
						}
						$relatedObject->attributes = $valueMap;
						if ($relatedObject->save()) {
							$a['relations'][$fieldParts[0]][] = $relatedObject->primaryKey;
						} else {
							\d($relatedObject); exit;
						}
					}
				}
			} else {
				$a[$fieldMap->localField] = $fieldMap->extractValue($foreignModel);
				if ($a[$fieldMap->localField] === false) {
					return false;
				}
			}
		}
		return $a;
	}


	public function getUnmappedKeys() {
		$u = [];
		$f = $this->unmappedForeignKeys;
		$l = $this->unmappedLocalKeys;
		if (!empty($f)) { $u['foreign'] = $f; }
		if (!empty($l)) { $u['local'] = $l; }
		return $u;
	}

	public function getUnmappedLocalKeys() {
		$u = array_diff(array_keys($this->localModel->getMetaData()->columns), array_keys($this->_map));
		unset($u[$this->localPrimaryKeyName]);
		return $u;
	}

	public function getUnmappedForeignKeys() {
		$mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
		$u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
		unset($u[$this->foreignPrimaryKeyName]);
		return $u;
	}

	public function getLocalPrimaryKeyName() {
		return $this->dummyLocalModel->tableSchema->primaryKey;
	}

	public function getForeignPrimaryKeyName() {
		return $this->foreignModel->meta->schema->primaryKey;
	}

	public function isRelationKey($key) {
		return substr($key, -3) === '_id';
	}

	public function generateKey(Model $foreignObject) {
		if (is_null($this->keyGenerator)) {
			$self = $this;
			$this->keyGenerator = function($foreignModel) use ($self) {
				return [$self->module->systemId, $foreignModel->tableName, $foreignModel->primaryKey];
			};
		}
		$keyGen = $this->keyGenerator;
		$return = $keyGen($foreignObject);

		if (isset($return)) {
			if (is_array($return)) {
				$return = implode('.', $return);
			}
			return $return;
		}
		return null;
	}

	public function getKeyTranslation(Model $foreignObject) {
		$key = $this->generateKey($foreignObject);
		if ($this->settings['universalKey']) {
			return KeyTranslation::findOne(['key' => $key]);
		} else {
			return KeyTranslation::findOne(['key' => $key, 'data_interface_id' => $this->module->collectorItem->interfaceObject->primaryKey]);
		}
	}


	public function saveKeyTranslation(Model $foreignObject, $localObject) {
		$key = $this->getKeyTranslation($foreignObject);
		if (!$key) {
			$key = new KeyTranslation;
			$key->data_interface_id = $this->module->collectorItem->interfaceObject->primaryKey;
			$key->registry_id = $localObject->primaryKey;
			$key->key = $this->generateKey($foreignObject);
			if (!$key->save()) {
				\d($key->attributes);
				\d($key->errors);
				exit;
				return false;
			}
		}
		return $key;
	}


	protected function loadForeignDataItems()
	{
		$this->_foreignDataItems = [];
		if ($this->lazyForeign) {
			$primaryKeys = $this->foreignModel->findPrimaryKeys($this->settings['foreignPullParams']);
			foreach ($primaryKeys as $primaryKey) {
				$this->createForeignDataItem(null, ['foreignPrimaryKey' => $primaryKey]);
			}
		} else {
			$foreignModels= $this->foreignModel->findAll($this->settings['foreignPullParams']);
			foreach ($foreignModels as $key => $model) {
				$this->createForeignDataItem($model, []);
			}
		}

	}

	public function createForeignDataItem($model, $config = [])
	{
		$config['isForeign'] = true;
		$config['foreignObject'] = $model;
		$object = $this->createDataItem($config);
		return $this->_foreignDataItems[$object->id] = $this->createDataItem($config);
	}

	public function createLocalDataItem($model, $config = [])
	{
		$config['isForeign'] = false;
		$config['localObject'] = $model;
		return $this->createDataItem($config);
	}

	protected function createDataItem($config = [])
	{
		if (!isset($config['class'])) {
			$config['class'] = $this->dataItemClass;
		}
		$config['dataSource'] = $this;
		return Yii::createObject($config);
	}

	protected function loadLocalDataItems()
	{
		$this->_localDataItems = [];
	}

	public function getModule()
	{
		return $this->dataSource->module;
	}
}
?>