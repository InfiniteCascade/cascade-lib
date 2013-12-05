<?php
namespace cascade\components\dataInterface\connectors;

use infinite\base\exceptions\Exception;

class DbMeta extends \infinite\base\Object {
	protected $_hasMany = array();
	protected $_hasOne = array();
	protected $_belongsTo = array();
	protected $_habtm = array();
	protected $_foreignTable;
	protected $_db;
	protected $_schema;

	static $_metas = array();

	public static function get($db, $foreignTable) {
		if (!isset(self::$_metas[$foreignTable])) {
			self::$_metas[$foreignTable] = new DbMeta($db, $foreignTable);
		}
		return self::$_metas[$foreignTable];
	}

	public function __construct($db, $foreignTable) {
		$this->_db = $db;
		$this->_foreignTable = $foreignTable;
		if (!isset($db->schema->tables[$foreignTable])) {
			throw new Exception("Foreign table does not exist {$foreignTable}!");
		}
		$this->_schema = $db->schema->tables[$foreignTable];
	}

	public function addHasMany(DbModel $foreignModel, $foreignKey, $params = array()) {
		$this->_hasMany[] = array('foreignModel' => $foreignModel, 'foreignKey' => $foreignKey, 'params' => $params);
	}

	public function addHasOne(DbModel $foreignModel, $foreignKey, $params = array()) {
		$this->_hasOne[] = array('foreignModel' => $foreignModel, 'foreignKey' => $foreignKey, 'params' => $params);

	}

	public function addBelongsTo(DbModel $foreignModel, $localKey, $params = array()) {
		$this->_belongsTo[] = array('foreignModel' => $foreignModel, 'localKey' => $localKey, 'params' => $params);
	}

	public function addHabtm(DbModel $foreignModel, DbModel $connectorModel, $localKey, $foreignKey, $params = array()) {
		$this->_habtm[] = array('foreignModel' => $foreignModel, 'connectorModel' => $connectorModel, 'localKey' => $localKey, 'foreignKey' => $foreignKey, 'params' => $params);
	}

	public function getHasMany() {
		return $this->_hasMany;
	}

	public function getHasOne() {
		return $this->_hasOne;
	}

	public function getBelongsTo() {
		return $this->_belongsTo;
	}

	public function getHabtm() {
		return $this->_habtm;
	}

	public function hasAttribute($name) {
		return isset($this->_schema->columns[$name]);
	}

	public function getAttributeKeys() {
		return array_keys($this->_schema->columns);
	}

	public function getSchema() {
		return $this->_schema;
	}
}
?>