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

	public function init()
	{
		$this->on(self::EVENT_LOAD_FOREIGN_DATA_ITEMS, [$this, 'loadForeignDataItems']);
		$this->on(self::EVENT_LOAD_LOCAL_DATA_ITEMS, [$this, 'loadLocalDataItems']);
		return parent::init();
	}

	public function getForeignDataItem($key)
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
		//var_dump($config);exit;
		return $this->foreignModel->findOne($config);
	}


	public function buildLocalAttributes(Model $foreignModel)
	{
		$a = [];
		foreach ($this->map as $localKey => $fieldMap) {
			if ($localKey === $this->localPrimaryKeyName) { continue; }
			if ($this->isRelationKey($fieldMap->foreignField)) {
				continue;
			}
			if (strpos($fieldMap->localField, ':') !== false) {
			} else {
				$a[$fieldMap->localField] = $fieldMap->extractValue($foreignModel);
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
				var_dump($key->attributes);
				var_dump($key->errors);
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