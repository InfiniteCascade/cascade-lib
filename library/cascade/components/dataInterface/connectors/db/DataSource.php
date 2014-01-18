<?php
namespace cascade\components\dataInterface\connectors\db;

use Yii;
use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\KeyTranslation;
use cascade\components\dataInterface\Action;

use infinite\helpers\ArrayHelper;

class DataSource extends \infinite\base\Object {
	public $fieldMapClass = 'cascade\\components\\dataInterface\\connectors\\db\\FieldMap';
	public $keyGenerator;

	public $module;
	protected $_dataInterface;
	protected $_localModel;
	protected $_foreignModel;
	protected $_map;
	protected $_settings;

	static $defaultSettings = [
		'direction' => 'to_local', // to_local, to_foreign, both
		'update' => true,
		'create' => true,
		'deleteLocal' => false,
		'deleteForeign' => false,
		'foreignPullParams' => [],
		'universalKey' => false
	];


	public function handleForeign($action, $f, $parent = null) {
		$key = $this->getKeyTranslation($f);
		$localAttributes = $this->buildLocalAttributes($f);
		if (!$action->keyInRegistry($key->key)) {
			if (empty($key->registry_id) || !($object = Registry::getObject($key->registry_id, true))) {
				// new local object!
				$objectModelClass = get_class($this->localModel);
				$object = new $objectModelClass;
				if ($object->hasBehavior('Blame')) {
					$object->detachBehavior('Blame');
				}

				$object->attributes = $localAttributes;
				if (!$object->save()) {
					$action->status->addError("Unable to save new $objectModelClass", ['newLocal' => $localAttributes]);
				} else {
					$key->registry_id = $object->primaryKey;
					$action->addRegistry($key->key, $object->primaryKey);
					if (!$key->save()) {
						$action->status->addError("Unable to save new key for $objectModelClass ({$object->primaryKey})", ['newLocal' => $localAttributes, 'key' => $key->attributes]);
						continue;
					}
				}
			} else {
				// update object

				$action->addRegistry($key->key, $object->primaryKey);
				$update = false;
				foreach ($localAttributes as $k => $v) {
					if ($object->{$k} !== $v) {
						$object->{$k} = $v;
						$update = true;
					}
				}
				if ($update AND !$object->save()) {
					$action->status->addError("Unable to update ". get_class($object) .'.'. $object->primaryKey, ['newLocal' => $localAttributes]);
				}
			}
		}

		if (!is_null($parent) AND !Relation::set($parent, $object, ['active' => 1])) {
			$action->status->addError("Unable to associate ". get_class($object) .'.'. $object->primaryKey ." to its parent", ['parent' => $parent, 'object' => $object]);
		}

		// foreign children
		$foreignChildren = $f->children;
		foreach ($foreignChildren as $childType => $children) {
			if (!isset($this->dataInterface->map[$childType])) {
				continue; // don't know how to map this child
			}
			$mapHandler = $this->dataInterface->map[$childType];
			foreach ($children as $child) {
				$mapHandler->handleForeign($action, $child, $object);
			}
		}
	}
	public function run($action = null) {
		if (is_null($action)) { $action = new Action(); }
		$this->settings = $action->settings;
		
		// start foreign
		$ff = $this->foreignModel->findAll($this->settings['foreignPullParams']);
		foreach ($ff as $k => $f) {
			$this->handleForeign($action, $f);
		}
		return true;
	}

	public function buildLocalAttributes(DbModel $foreignModel) {
		$a = [];
		foreach ($this->map as $localKey => $foreignSettings) {
			if ($localKey === $this->localPrimaryKeyName) { continue; }
			if ($this->isRelationKey($localKey)) {
				continue;
			}
			$a[$localKey] = $this->getValue($foreignModel, $foreignSettings);
		}
		return $a;
	}

	public function isRelationKey($key) {
		return substr($key, -3) === '_id';
	}

	public function getValue(DbModel $foreignModel, $settings) {
		$value = null;
		if (isset($settings['value'])) {
			$value = $settings['value']($foreignModel, $this);
		} elseif (isset($settings['foreignKey'])) {
			$value = (isset($foreignModel->{$settings['foreignKey']}) ? $foreignModel->{$settings['foreignKey']} : null);
		}
		if (isset($settings['filter'])) {
			$value = $settings['filter']($value);
		}
		return $value;
	}

	public function generateKey(DbModel $foreignObject) {
		if (is_null($this->keyGenerator)) {
			$this->keyGenerator = function($foreignModel) {
				return [$foreignModel->foreignTable, $foreignModel->primaryKey];
			};
		}
		$keyGen = $this->keyGenerator;
		return $keyGen($foreignObject);
	}

	public function getKeyTranslation(DbModel $foreignObject) {
		$key = $this->generateKey($foreignObject);
		if ($this->settings['universalKey']) {
			return KeyTranslation::get($key);
		} else {
			return KeyTranslation::get($key, $this->dataInterface->interfaceItem->interfaceObject);
		}
	}

	public function getDataInterface() {
		return $this->_dataInterface;
	}

	public function setSettings($settings) {
		if (is_null($this->_settings)) {
			$this->_settings = self::$defaultSettings;
		}
		if (!is_array($settings)) { return true; }
		$this->_settings = array_merge($this->_settings, $settings);
		return true;
	}

	public function getSettings() {
		return $this->_settings;
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
		$u = array_diff(array_keys($this->localModel->getMetaData()->columns), array_keys($this->_m));
		unset($u[$this->localPrimaryKeyName]);
		return $u;
	}

	public function getUnmappedForeignKeys() {
		$mappedForeign = ArrayHelper::getColumn($this->_m, 'foreignKey');
		$u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
		unset($u[$this->foreignPrimaryKeyName]);
		return $u;
	}

	public function getLocalPrimaryKeyName() {
		return $this->localModel->getMetaData()->tableSchema->primaryKey;
	}

	public function getForeignPrimaryKeyName() {
		return $this->foreignModel->meta->schema->primaryKey;
	}

	public function getLocalModel() {
		return $this->_localModel;
	}

	public function setLocalModel($value) {
		$this->_localModel = $value;
	}

	public function setForeignModel($value) {
		$this->_foreignModel = $value;
	}

	public function getForeignModel() {
		return $this->_foreignModel;
	}

	public function setMap($m) {
		foreach ($m as $k => $v) {
			$fieldMap = $v;
			if (!isset($fieldMap['class'])) {
				$fieldMap['class'] = $this->fieldMapClass;
			}
			$fieldMap['map'] = $this;
			$fieldMap = Yii::createObject($fieldMap);
			$this->_map[] = $fieldMap;
		}
		return true;
	}

	public function getMap() {
		return $this->_m;
	}
}
?>