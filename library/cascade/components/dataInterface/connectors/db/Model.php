<?php
namespace cascade\components\dataInterface\connectors\db;

use infinite\base\exceptions\Exception;
use yii\db\Query;

class Model extends \infinite\base\Object {
	protected $_interface;
	protected $_tableName;
	protected $_meta;
	protected $_attributes;
	protected $_keys;
	protected $_children;

	public function init()
	{
		parent::init();
		$this->_meta = Meta::get($this->interface->db, $this->tableName);
	}

	public function __clone()
	{
		$this->reset();
	}


	public function __get($name) {
		if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
		if (isset($this->meta) && $this->meta->hasAttribute($name)) {
			$this->_attributes[$name] = $value;
			return true;
		}
		return parent::__set($name, $value);
	}

	public function __isset($name) {
		if(isset($this->meta) && $this->meta->hasAttribute($name) && isset($this->_attributes[$name])) {
			return true;
		}
		return parent::__isset($name);
	}

	public function __unset($name) {
		if(isset($this->meta) && $this->meta->hasAttribute($name)) {
			unset($this->_attributes[$name]);
			return true;
		}
		return parent::__unset($name);
	}

	public function settableName($value)
	{
		$this->_tableName = $value;
	}

	public function setInterface($value)
	{
		$this->_interface = $value;
	}

	public function reset()
	{
		$this->_attributes = [];
	}

	public function setAttributes($value)
	{
		foreach ($value as $key => $val) {
			$this->{$key} = $val;
		}
	}

	public function getChildren() {
		if (is_null($this->_children)) {
			$children = [];
			// for this application, there is no distinction between hasOne and hasMany on the database level
			$hasMany = array_merge($this->meta->hasMany, $this->meta->hasOne);
			foreach ($hasMany as $r) {
				$query = [
					'where' => $r['foreignKey'] .'=:foreignKeyId',
					'params' => [':foreignKeyId' => $this->primaryKey]
				];
				$children[$r['foreignModel']->tableName] = $r['foreignModel']->findAll($query);
			}
			$habtm = $this->meta->habtm;


			$this->_children = $children;
		}

		return $this->_children;
	}

	public function getPrimaryKey() {
		$pk = $this->meta->schema->primaryKey;
		return $this->attributes[$pk];
	}

	public function populateRecord($attributes) {
		$clone = clone $this;
		$clone->attributes = $attributes;
		return $clone;
	}

	public function populateRecords($results) {
		$r = [];
		foreach ($results as $o) {
			$r[] = $this->populateRecord($o);
		}
		return $r;
	}

	public function getAttributes() {
		$a = [];
		foreach ($this->meta->attributeKeys as $k) {
			$a[$k] = null;
			if (is_array($this->_attributes) && isset($this->_attributes[$k])) {
				$a[$k] = $this->_attributes[$k];
			}
		}
		return $a;
	}

	public function getMeta() {
		return $this->_meta;
	}

	public function getInterface() {
		return $this->_interface;
	}

	public function findAll($params = []) {
		$q = new Query;
		$q->select('*');
		$q->from($this->_tableName);
		foreach ($params as $k => $v) {
			$q->{$k} = $v;
		}
		return $this->populateRecords($q->all($this->interface->db));
	}

	public function getTableName() {
		return $this->_tableName;
	}
	
}
?>