<?php
namespace cascade\components\dataInterface;

use Yii;
use yii\helpers\Inflector;
use infinite\base\exceptions\Exception;


abstract class DbModule extends Module {
	public $dataSourceClass = 'cascade\\components\\dataInterface\\connectors\\db\\DataSource';
	public $dbConfig = [];

	protected $_models;
	protected $_db;
	protected $_dataSources;

	abstract public function dataSources();

	public function getDataSources()
	{
		if (is_null($this->_dataSources)) {
			$this->_dataSources = [];
			foreach ($this->dataSources() as $localKey => $dataSource) {
				if (!isset($dataSource['class'])) {
					$dataSource['class'] = $this->dataSourceClass;
				}
				$dataSource['module'] = $this;
				$this->_dataSources[$localKey] = Yii::createObject($dataSource);
			}
		}
		return $this->_dataSources;
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
}
?>