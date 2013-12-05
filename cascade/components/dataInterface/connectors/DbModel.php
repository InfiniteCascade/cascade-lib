<?php
namespace cascade\components\dataInterface\connectors;

use infinite\base\exceptions\Exception;

class DbModel extends \infinite\base\Object {
	protected $_interface;
	protected $_foreignTable;
	protected $_meta;
	protected $_attributes;
	protected $_keys;
	protected $_children;

	public function __construct($interface, $foreignTable, $attributes = null) {
		$this->_foreignTable = $foreignTable;
		$this->_interface = $interface;
		$this->_attributes = $attributes;
		$this->_meta = DbMeta::get($this->interface->db, $foreignTable);
	}

	public function __get($name) {
		if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
		if ($this->meta->hasAttribute($name)) {
			$this->_attributes[$name] = $value;
			return true;
		}
		return parent::__get($name);
	}

	public function __isset($name) {
		if($this->meta->hasAttribute($name) && isset($this->_attributes[$name])) {
			return true;
		}
		return parent::__isset($name);
	}

	public function __unset($name) {
		if($this->meta->hasAttribute($name)) {
			unset($this->_attributes[$name]);
			return true;
		}
		return parent::__unset($name);
	}

	public function getChildren() {
		if (is_null($this->_children)) {
			$children = array();
			// for this application, there is no distinction between hasOne and hasMany on the database level
			$hasMany = array_merge($this->meta->hasMany, $this->meta->hasOne);
			foreach ($hasMany as $r) {
				$query = array(
					'where' => $r['foreignKey'] .'=:foreignKeyId',
					'params' => array(':foreignKeyId' => $this->primaryKey)
				);
				$children[$r['foreignModel']->foreignTable] = $r['foreignModel']->findAll($query);
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
		return new DbModel($this->_interface, $this->_foreignTable, $attributes);
	}

	public function populateRecords($results) {
		$r = array();
		foreach ($results as $o) {
			$r[] = $this->populateRecord($o);
		}
		return $r;
	}

	public function getAttributes() {
		$a = array();
		foreach ($this->meta->attributeKeys as $k) {
			$a[$k] = null;
			if (is_array($this->_attributes) AND isset($this->_attributes[$k])) {
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

	public function findAll($params = array()) {
		$c = $this->interface->db->createCommand();
		$c->select('*');
		$c->from($this->_foreignTable);
		foreach ($params as $k => $v) {
			$c->{$k} = $v;
		}
		return $this->populateRecords($c->queryAll());
	}

	public function getForeignTable() {
		return $this->_foreignTable;
	}
	
}
?>