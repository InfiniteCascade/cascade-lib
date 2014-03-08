<?php
namespace cascade\components\dataInterface\connectors\db;

use Yii;
use yii\helpers\Inflector;
use infinite\base\exceptions\Exception;
use cascade\components\dataInterface\Action;
use cascade\components\dataInterface\Module as BaseModule;

abstract class Module extends BaseModule {
	public $dataSourceClass = 'cascade\\components\\dataInterface\\connectors\\db\\DataSource';
	public $dbConfig = [];
	protected $_action;

	protected $_models;
	protected $_db;
	protected $_dataSources;

	abstract public function dataSources();

	public function getKeyTranslation(Model $foreignObject) {
		$key = $this->generateKey($foreignObject);
		if ($this->settings['universalKey']) {
			return KeyTranslation::get($key);
		} else {
			return KeyTranslation::get($key, $this->dataInterface->interfaceItem->interfaceObject);
		}
	}
	
	public function run(Action $action)
	{
		$this->_action = $action;
		$total = 0;
		foreach ($this->dataSources as $source)
		{
			$total += $source->total;
		}

		$action->progressTotal = $total;
		foreach ($this->dataSources as $source)
		{
			if ($source->settings['direction'] === 'to_local') {
				$prefix = 'Importing';
			} elseif ($source->settings['direction'] === 'to_foreign') {
				$prefix = 'Exporting';
			} else {
				$prefix = 'Syncing';
			}
			$action->progressPrefix = "{$prefix} {$source->name}...";
			$source->run();
		}
	}

	public function getDataSource($tableName)
	{
		$model = $this->getForeignModelName($tableName);
		if (isset($this->dataSources[$model])) {
			return $this->dataSources[$model];
		}
		foreach ($this->dataSources as $dataSource) {
			if ($dataSource->foreignModel->tableName === $tableName) {
				return $dataSource;
			}
		}
		return false;
	}

	public function getDataSources()
	{
		if (is_null($this->_dataSources)) {
			$this->_dataSources = [];
			foreach ($this->dataSources() as $foreignModel => $dataSource) {
				if (!isset($dataSource['class'])) {
					$dataSource['class'] = $this->dataSourceClass;
				}
				$dataSource['name'] = $foreignModel;
				$dataSource['foreignModel'] = $this->getForeignModel($foreignModel);
				$dataSource['module'] = $this;
				$this->_dataSources[$foreignModel] = Yii::createObject($dataSource);
			}
		}
		return $this->_dataSources;
	}

	public function updateLocalObject($relatedType, $foreignPrimaryKey, $valueMap, $fieldMap, $localModel)
	{
		$localModelClass = $relatedType->primaryModel;
		// @todo eventually we'll probably take some keys out of this
		$searchMap = $valueMap;
		if (isset($fieldMap->searchFields) && is_array($fieldMap->searchFields)) {
			foreach ($searchMap as $field => $value) {
				if (!in_array($field, $fieldMap->searchFields)) {
					unset($searchMap[$field]);
				}
			}
		}

		$fieldParts = explode(':', $fieldMap->localField);
		if ($fieldParts[0] === 'child') {
			$currentRelationsFunction = 'child';
		} else {
			$currentRelationsFunction = 'parent';
		}
		// first, lets see if it exists
		$relatedObject = null;
		$currentRelation = false;
		if (!empty($localModel) && !$localModel->isNewRecord) {
			$test = $localModel->{$currentRelationsFunction}($relatedType->primaryModel, [], ['where' => $searchMap, 'disableAccessCheck' => 1]);
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
			return $relatedObject;
		} else {
			\d($relatedObject); exit;
			return false;
		}
	}

	public function getLocalObject($localModelClass, $foreignPrimaryKey)
	{
		$dataSource = $this->getLocalDataSource($localModelClass);
		if ($dataSource && ($foreignDataItem = $dataSource->getForeignDataItem($foreignPrimaryKey))) {
			return $foreignDataItem->handle(true);
		}
		return false;
	}

	public function getForeignObject($foreignModelClass, $foreignPrimaryKey)
	{
		$dataSource = $this->getForeignDataSource($foreignModelClass);
		if ($dataSource && ($foreignDataItem = $dataSource->getForeignDataItem($foreignPrimaryKey))) {
			return $foreignDataItem->handle(true);
		}
		return false;
	}

	public function getLocalDataSource($localModelClass)
	{
		foreach ($this->dataSources as $dataSource) {
			if ($dataSource->localModel === $localModelClass) {
				return $dataSource;
			}
		}
		return false;
	}

	public function getForeignDataSource($foreignModelClass)
	{
		foreach ($this->dataSources as $dataSource) {
			if ($dataSource->foreignModel->modelName === $foreignModelClass) {
				return $dataSource;
			}
		}
		return false;
	}

	public function getForeignModel($model)
	{
		$models = $this->foreignModels;
		if (isset($models[$model])) {
			return $models[$model];
		}
		return false;
	}

	public function getForeignModelsConfig()
	{
		return [];
	}
	
	public function getForeignModelConfig($tableName, $modelName)
	{
		$config = ['class' => 'cascade\\components\\dataInterface\\connectors\\db\\Model'];
		if (isset($this->foreignModelsConfig[$modelName])) {
			$config = array_merge($config, $this->foreignModelsConfig[$modelName]);
		}
		$config['modelName'] = $modelName;
		$config['tableName'] = $tableName;
		$config['interface'] = $this;
		return $config;
	}

	public function getForeignModelName($tableName)
	{
		return Inflector::singularize(Inflector::id2camel($tableName, '_'));
	}
	
	public function getForeignModels()
	{
		if (is_null($this->_models)) {
			$this->_models = [];
			foreach ($this->db->schema->getTableNames() as $tableName) {
				$modelName = $this->getForeignModelName($tableName);
				$this->_models[$modelName] = Yii::createObject($this->getForeignModelConfig($tableName, $modelName));
			}
		}
		return $this->_models;
	}
	
	public function getDb()
	{
		if (is_null($this->_db)) {
			if (!isset($this->dbConfig['class'])) {
				$this->dbConfig['class'] = 'cascade\\components\\dataInterface\\connectors\\db\\Connection';
			}
			$this->_db = Yii::createObject($this->dbConfig);
			$this->_db->open();
		}
		if (empty($this->_db) || !$this->_db->isActive) {
			throw new Exception("Unable to connect to foreign database.");
		}
		return $this->_db;
	}

	public function getAction()
	{
		if (is_null($this->_action)) {
			$this->_action = new Action;
		}
		return $this->_action;
	}
}
?>