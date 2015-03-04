<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use infinite\base\exceptions\Exception;

/**
 * Meta [@doctodo write class description for Meta].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \infinite\base\Object
{
    /**
     */
    protected $_hasMany = [];
    /**
     */
    protected $_hasOne = [];
    /**
     */
    protected $_belongsTo = [];
    /**
     */
    protected $_foreignTable;
    /**
     */
    protected $_db;
    /**
     */
    protected $_schema;
    /**
     */
    protected $_interface;

    /*
     */
    public static $_metas = [];

    /**
     * Get.
     */
    public static function get($interface, $foreignTable)
    {
        if (!isset(self::$_metas[$foreignTable])) {
            self::$_metas[$foreignTable] = new static($interface, $foreignTable);
        }

        return self::$_metas[$foreignTable];
    }

    /**
     * @inheritdoc
     */
    public function __construct($interface, $foreignTable)
    {
        $this->_db = $interface->db;
        $this->_interface = $interface;
        $this->_foreignTable = $foreignTable;
        if (!($this->_schema = $interface->db->schema->getTableSchema($foreignTable))) {
            throw new Exception("Foreign table does not exist {$foreignTable}!");
        }
    }

    /**
     * Set has many.
     */
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

    /**
     * Get has many.
     */
    public function getHasMany()
    {
        return $this->_hasMany;
    }

    /**
     * Get has one.
     */
    public function getHasOne()
    {
        return $this->_hasOne;
    }

    /**
     * Get belongs to.
     */
    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    /**
     *
     */
    public function hasAttribute($name)
    {
        return isset($this->_schema->columns[$name]);
    }

    /**
     * Get attribute keys.
     */
    public function getAttributeKeys()
    {
        return array_keys($this->_schema->columns);
    }

    /**
     * Get schema.
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Set interface.
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface.
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
