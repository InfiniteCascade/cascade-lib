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
 * Meta [[@doctodo class_description:cascade\components\dataInterface\connectors\db\Meta]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \infinite\base\Object
{
    /**
     * @var [[@doctodo var_type:_hasMany]] [[@doctodo var_description:_hasMany]]
     */
    protected $_hasMany = [];
    /**
     * @var [[@doctodo var_type:_hasOne]] [[@doctodo var_description:_hasOne]]
     */
    protected $_hasOne = [];
    /**
     * @var [[@doctodo var_type:_belongsTo]] [[@doctodo var_description:_belongsTo]]
     */
    protected $_belongsTo = [];
    /**
     * @var [[@doctodo var_type:_foreignTable]] [[@doctodo var_description:_foreignTable]]
     */
    protected $_foreignTable;
    /**
     * @var [[@doctodo var_type:_db]] [[@doctodo var_description:_db]]
     */
    protected $_db;
    /**
     * @var [[@doctodo var_type:_schema]] [[@doctodo var_description:_schema]]
     */
    protected $_schema;
    /**
     * @var [[@doctodo var_type:_interface]] [[@doctodo var_description:_interface]]
     */
    protected $_interface;

    
    /**
     * @var [[@doctodo var_type:_metas]] [[@doctodo var_description:_metas]]
     */
    public static $_metas = [];

    /**
     * Get.
     *
     * @return [[@doctodo return_type:get]] [[@doctodo return_description:get]]
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
     *
     * @return [[@doctodo return_type:getHasMany]] [[@doctodo return_description:getHasMany]]
     */
    public function getHasMany()
    {
        return $this->_hasMany;
    }

    /**
     * Get has one.
     *
     * @return [[@doctodo return_type:getHasOne]] [[@doctodo return_description:getHasOne]]
     */
    public function getHasOne()
    {
        return $this->_hasOne;
    }

    /**
     * Get belongs to.
     *
     * @return [[@doctodo return_type:getBelongsTo]] [[@doctodo return_description:getBelongsTo]]
     */
    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    /**
     * [[@doctodo method_description:hasAttribute]].
     *
     * @return [[@doctodo return_type:hasAttribute]] [[@doctodo return_description:hasAttribute]]
     */
    public function hasAttribute($name)
    {
        return isset($this->_schema->columns[$name]);
    }

    /**
     * Get attribute keys.
     *
     * @return [[@doctodo return_type:getAttributeKeys]] [[@doctodo return_description:getAttributeKeys]]
     */
    public function getAttributeKeys()
    {
        return array_keys($this->_schema->columns);
    }

    /**
     * Get schema.
     *
     * @return [[@doctodo return_type:getSchema]] [[@doctodo return_description:getSchema]]
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
     *
     * @return [[@doctodo return_type:getInterface]] [[@doctodo return_description:getInterface]]
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
