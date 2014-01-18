<?php
namespace cascade\components\dataInterface;

use Yii;
use infinite\base\exceptions\Exception;


abstract class DbModule extends Module {
	public $dbConfig = [];

	protected $_models;
	protected $_db;

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
		$config = ['class' => 'cascade\\components\\dataInterface\\connectors\\DbModel'];
		if (isset($this->foreignModelsConfig[$modelName])) {
			$config = array_merge($config, $this->foreignModelsConfig[$modelName]);
		}
		$config['tableName'] = $tableName;
		$config['interface'] = $this;
		return $config;
	}

	public function getForeignModelName($tableName)
	{
		return $tableName;
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
				$this->dbConfig['class'] = 'cascade\\components\\dataInterface\\connectors\\DbConnection';
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