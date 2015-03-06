<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

/**
 * Model [[@doctodo class_description:cascade\components\dataInterface\connectors\generic\Model]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Model extends \infinite\base\Object
{
    /**
     * @var [[@doctodo var_type:modelName]] [[@doctodo var_description:modelName]]
     */
    public $modelName;
    /**
     * @var [[@doctodo var_type:_interface]] [[@doctodo var_description:_interface]]
     */
    protected $_interface;
    /**
     * @var [[@doctodo var_type:_attributes]] [[@doctodo var_description:_attributes]]
     */
    protected $_attributes;
    /**
     * @var [[@doctodo var_type:_children]] [[@doctodo var_description:_children]]
     */
    protected $_children;

    /**
     * [[@doctodo method_description:__clone]].
     */
    public function __clone()
    {
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;

            return true;
        }

        return parent::__set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if ($this->hasAttribute($name) && isset($this->_attributes[$name])) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->_attributes[$name]);

            return true;
        }

        return parent::__unset($name);
    }

    /**
     * Get table name.
     *
     * @return [[@doctodo return_type:getTableName]] [[@doctodo return_description:getTableName]]
     */
    public function getTableName()
    {
        return static::baseClassName();
    }

    /**
     * [[@doctodo method_description:hasAttribute]].
     *
     * @param [[@doctodo param_type:attribute]] $attribute [[@doctodo param_description:attribute]]
     *
     * @return [[@doctodo return_type:hasAttribute]] [[@doctodo return_description:hasAttribute]]
     */
    public function hasAttribute($attribute)
    {
        return !$this->hasProperty($attribute, true);
    }

    /**
     * Set interface.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * [[@doctodo method_description:reset]].
     */
    public function reset()
    {
        $this->_attributes = [];
    }

    /**
     * Set attributes.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setAttributes($value)
    {
        foreach ($value as $key => $val) {
            $this->{$key} = $val;
        }
    }

    /**
     * [[@doctodo method_description:primaryKey]].
     */
    abstract public function primaryKey();
    /**
     * Get primary key.
     */
    abstract public function getPrimaryKey();

    /**
     * [[@doctodo method_description:populateRecord]].
     *
     * @param [[@doctodo param_type:attributes]] $attributes [[@doctodo param_description:attributes]]
     *
     * @return [[@doctodo return_type:populateRecord]] [[@doctodo return_description:populateRecord]]
     */
    public function populateRecord($attributes)
    {
        if ($attributes === false) {
            return false;
        }
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    /**
     * [[@doctodo method_description:populateRecords]].
     *
     * @param [[@doctodo param_type:results]] $results [[@doctodo param_description:results]]
     *
     * @return [[@doctodo return_type:populateRecords]] [[@doctodo return_description:populateRecords]]
     */
    public function populateRecords($results)
    {
        $r = [];
        foreach ($results as $o) {
            $r[] = $this->populateRecord($o);
        }

        return $r;
    }

    /**
     * Get attributes.
     *
     * @return [[@doctodo return_type:getAttributes]] [[@doctodo return_description:getAttributes]]
     */
    public function getAttributes()
    {
        return $this->_attributes;
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
