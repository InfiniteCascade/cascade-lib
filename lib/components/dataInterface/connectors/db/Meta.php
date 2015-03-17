<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use canis\base\exceptions\Exception;

/**
 * Meta meta for the database data interface connector.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \canis\base\Object
{
    /**
     * @var array has many relationships for model meta
     */
    protected $_hasMany = [];
    /**
     * @var array has one relationships for model meta
     */
    protected $_hasOne = [];
    /**
     * @var array belongs to relationships for model meta
     */
    protected $_belongsTo = [];
    /**
     * @var string foreign table name
     */
    protected $_foreignTable;
    /**
     * @var Connection database connection for this model
     */
    protected $_db;
    /**
     * @var Schema database schema for the table
     */
    protected $_schema;
    /**
     * @var Item database item for the model
     */
    protected $_interface;

    /**
     * @var array static collection of all meta objects
     */
    public static $_metas = [];

    /**
     * Get.
     *
     * @param InterfaceItem    $interface    the interface object
     * @param string $foreignTable the foreign table name
     *
     * @return static meta object for the given interface and foreign table
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
     *
     * @param array $config relationship configuration
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
     *
     * @return array has many relationships for model meta
     */
    public function getHasMany()
    {
        return $this->_hasMany;
    }

    /**
     * Get has one.
     *
     * @return array has one relationships for model meta
     */
    public function getHasOne()
    {
        return $this->_hasOne;
    }

    /**
     * Get belongs to.
     *
     * @return array belongs to relationships for model meta
     */
    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    /**
     * Check if data source has a certain attribute.
     *
     * @param string $name attribute name
     *
     * @return bool if the attribute exists
     */
    public function hasAttribute($name)
    {
        return isset($this->_schema->columns[$name]);
    }

    /**
     * Get attribute keys.
     *
     * @return array of column names
     */
    public function getAttributeKeys()
    {
        return array_keys($this->_schema->columns);
    }

    /**
     * Get schema.
     *
     * @return Schema database schema
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Set interface.
     *
     * @param InterfaceItem $value interface item object
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface.
     *
     * @return Item data interface Item
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
