<?php
namespace cascade\components\dataInterface\connectors\db;

use infinite\base\exceptions\Exception;

class Meta extends \infinite\base\Object
{
    protected $_hasMany = [];
    protected $_hasOne = [];
    protected $_belongsTo = [];
    protected $_foreignTable;
    protected $_db;
    protected $_schema;
    protected $_interface;

    static $_metas = [];

    public static function get($interface, $foreignTable)
    {
        if (!isset(self::$_metas[$foreignTable])) {
            self::$_metas[$foreignTable] = new Meta($interface, $foreignTable);
        }

        return self::$_metas[$foreignTable];
    }

    public function __construct($interface, $foreignTable)
    {
        $this->_db = $interface->db;
        $this->_interface = $interface;
        $this->_foreignTable = $foreignTable;
        if (!($this->_schema = $interface->db->schema->getTableSchema($foreignTable))) {
            throw new Exception("Foreign table does not exist {$foreignTable}!");
        }

    }

    public function setHasMany($config)
    {
        foreach ($config as $value) {
            $this->_hasMany[] = $value;
        }
    }

    // public function addHasMany(Model $foreignModel, $foreignKey, $settings = []) {
    // 	$settings['foreignModel'] = $foreignModel;
    // 	$settings['foreignKey'] = $foreignKey;
    // 	$this->_hasMany[] = $settings;
    // }

    // public function addHasOne(Model $foreignModel, $foreignKey, $params = []) {
    // 	$this->_hasOne[] = ['foreignModel' => $foreignModel, 'foreignKey' => $foreignKey, 'params' => $params];

    // }

    // public function addBelongsTo(Model $foreignModel, $localKey, $params = []) {
    // 	$this->_belongsTo[] = ['foreignModel' => $foreignModel, 'localKey' => $localKey, 'params' => $params];
    // }

    // public function addHabtm(Model $foreignModel, Model $connectorModel, $localKey, $foreignKey, $params = []) {
    // 	$this->_habtm[] = ['foreignModel' => $foreignModel, 'connectorModel' => $connectorModel, 'localKey' => $localKey, 'foreignKey' => $foreignKey, 'params' => $params];
    // }

    public function getHasMany()
    {
        return $this->_hasMany;
    }

    public function getHasOne()
    {
        return $this->_hasOne;
    }

    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    public function hasAttribute($name)
    {
        return isset($this->_schema->columns[$name]);
    }

    public function getAttributeKeys()
    {
        return array_keys($this->_schema->columns);
    }

    public function getSchema()
    {
        return $this->_schema;
    }

    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    public function getInterface()
    {
        return $this->_interface;
    }
}
