<?php
namespace cascade\components\dataInterface\connectors\db;

use infinite\base\exceptions\Exception;

class Meta extends \infinite\base\Object {
	protected $_hasMany = [];
	protected $_hasOne = [];
	protected $_belongsTo = [];
	protected $_habtm = [];
	protected $_foreignTable;
	protected $_db;
	protected $_schema;

	static $_metas = [];

	public static function get($db, $foreignTable) {
		if (!isset(self::$_metas[$foreignTable])) {
			self::$_metas[$foreignTable] = new Meta($db, $foreignTable);
		}
		return self::$_metas[$foreignTable];
	}

	public function __construct($db, $foreignTable) {
		$this->_db = $db;
		$this->_foreignTable = $foreignTable;
		if (!($this->_schema = $db->schema->getTableSchema($foreignTable))) {
			throw new Exception("Foreign table does not exist {$foreignTable}!");
		}
		
	}

	public function addHasMany(Model $foreignModel, $foreignKey, $params = []) {
		$this->_hasMany[] = ['foreignModel' => $foreignModel, 'foreignKey' => $foreignKey, 'params' => $params];
	}

	public function addHasOne(Model $foreignModel, $foreignKey, $params = []) {
		$this->_hasOne[] = ['foreignModel' => $foreignModel, 'foreignKey' => $foreignKey, 'params' => $params];

	}

	public function addBelongsTo(Model $foreignModel, $localKey, $params = []) {
		$this->_belongsTo[] = ['foreignModel' => $foreignModel, 'localKey' => $localKey, 'params' => $params];
	}

	public function addHabtm(Model $foreignModel, Model $connectorModel, $localKey, $foreignKey, $params = []) {
		$this->_habtm[] = ['foreignModel' => $foreignModel, 'connectorModel' => $connectorModel, 'localKey' => $localKey, 'foreignKey' => $foreignKey, 'params' => $params];
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