<?php
namespace cascade\components\dataInterface\connectors\db;

use cascade\components\dataInterface\RecursionException;
use cascade\components\dataInterface\MissingItemException;

class DataItem extends \cascade\components\dataInterface\DataItem {
	protected $_isLoadingForeignObject = false;
	protected $_isLoadingLocalObject = false;

	public function init()
	{
		$this->on(self::EVENT_LOAD_FOREIGN_OBJECT, [$this, 'loadForeignObject']);
		$this->on(self::EVENT_LOAD_LOCAL_OBJECT, [$this, 'loadLocalObject']);
		return parent::init();
	}


	protected function handleForeign()
	{
		if (!$this->foreignObject) {
			return false;
		}

		// foreign to local

		// find or start up local object
		$localModel = $this->dataSource->localModel;
		if (is_null($localModel)) {
			var_dump($this->dataSource);exit;
		}
		if (!isset($this->localObject)) {
		//	throw new \Exception("no new objects on this pass!");
			$this->localObject = new $localModel;
		}

		$attributes = $this->dataSource->buildLocalAttributes($this->foreignObject);
		if (empty($attributes)) {
			return false;
		}
		// load local object
		foreach ($attributes as $key => $value) {
			$this->localObject->$key = $value;
		}

		// save local object
		if (!$this->localObject->save()) {
			var_dump($this->localObject->errors);exit;
			return false;
		}

		// save foreign key map
		if (!$this->dataSource->saveKeyTranslation($this->foreignObject, $this->localObject)) {
			throw new \Exception("Unable to save key translation!");
		}

		// loop through children
		foreach ($this->foreignObject->children as $table => $children) {
			$dataSource = $this->module->getDataSource($table);
			if (empty($dataSource) || !$dataSource->isRead()) { continue; }
			foreach ($children as $child) {
				// let the handler figure it out
			}
		} 

		return $this->localObject;
	}

	protected function handleLocal()
	{
		// local to foreign

		// find 

		return true;
	}

	protected function loadForeignObject()
	{
		if ($this->_isLoadingForeignObject) {
			throw new RecursionException('Ran into recursion while loading foreign object');
		}
		$this->_isLoadingForeignObject = true;
		if (isset($this->foreignPrimaryKey)) {
			$foreignObject = $this->dataSource->getForeignDataItem($this->foreignPrimaryKey);
			if ($foreignObject) {
				$this->foreignObject = $foreignObject;
				if ($foreignObject->primaryKey !== $this->foreignPrimaryKey) {
					var_dump([$this->foreignPrimaryKey, $foreignObject]);exit;
				}
			}
		}
		if (empty($this->_foreignObject)) {
			throw new MissingItemException('Foreign item could not be found: '. $this->foreignPrimaryKey);
		}
		$this->_isLoadingForeignObject = false;
	}

	protected function loadLocalObject()
	{
		if ($this->_isLoadingLocalObject) {
			throw new RecursionException('Ran into recursion while loading local object');
		}
		$this->_isLoadingLocalObject = true;
		if (isset($this->foreignObject)) {
			$keyTranslation = $this->dataSource->getKeyTranslation($this->foreignObject);
			if (!empty($keyTranslation) && ($localObject = $keyTranslation->object)) {
				$this->_localObject = $localObject;
			}
		}
		$this->_isLoadingLocalObject = false;
	}
}
?>