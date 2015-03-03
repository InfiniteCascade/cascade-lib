<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use infinite\base\exceptions\Exception;

/**
 * Meta [@doctodo write class description for Meta]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \infinite\base\Object
{
    /**
     * @var __var__hasMany_type__ __var__hasMany_description__
     */
    protected $_hasMany = [];
    /**
     * @var __var__hasOne_type__ __var__hasOne_description__
     */
    protected $_hasOne = [];
    /**
     * @var __var__belongsTo_type__ __var__belongsTo_description__
     */
    protected $_belongsTo = [];
    /**
     * @var __var__foreignTable_type__ __var__foreignTable_description__
     */
    protected $_foreignTable;
    /**
     * @var __var__db_type__ __var__db_description__
     */
    protected $_db;
    /**
     * @var __var__schema_type__ __var__schema_description__
     */
    protected $_schema;
    /**
     * @var __var__interface_type__ __var__interface_description__
     */
    protected $_interface;

    /**
     * @var __var__metas_type__ __var__metas_description__
     */
    static $_metas = [];

    /**
     * Get
     * @param __param_interface_type__    $interface    __param_interface_description__
     * @param __param_foreignTable_type__ $foreignTable __param_foreignTable_description__
     * @return __return_get_type__         __return_get_description__
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
     * Set has many
     * @param __param_config_type__ $config __param_config_description__
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
     * Get has many
     * @return __return_getHasMany_type__ __return_getHasMany_description__
     */
    public function getHasMany()
    {
        return $this->_hasMany;
    }

    /**
     * Get has one
     * @return __return_getHasOne_type__ __return_getHasOne_description__
     */
    public function getHasOne()
    {
        return $this->_hasOne;
    }

    /**
     * Get belongs to
     * @return __return_getBelongsTo_type__ __return_getBelongsTo_description__
     */
    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    /**
     * __method_hasAttribute_description__
     * @param __param_name_type__          $name __param_name_description__
     * @return __return_hasAttribute_type__ __return_hasAttribute_description__
     */
    public function hasAttribute($name)
    {
        return isset($this->_schema->columns[$name]);
    }

    /**
     * Get attribute keys
     * @return __return_getAttributeKeys_type__ __return_getAttributeKeys_description__
     */
    public function getAttributeKeys()
    {
        return array_keys($this->_schema->columns);
    }

    /**
     * Get schema
     * @return __return_getSchema_type__ __return_getSchema_description__
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Set interface
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface
     * @return __return_getInterface_type__ __return_getInterface_description__
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
