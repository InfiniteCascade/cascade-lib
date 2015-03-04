<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

/**
 * Model [@doctodo write class description for Model].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Model extends \infinite\base\Object
{
    /**
     */
    public $modelName;
    /**
     */
    protected $_interface;
    /**
     */
    protected $_attributes;
    /**
     */
    protected $_children;

    /**
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

    public function getTableName()
    {
        return static::baseClassName();
    }

    public function hasAttribute($attribute)
    {
        return !$this->hasProperty($attribute, true);
    }

    /**
     * Set interface.
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     */
    public function reset()
    {
        $this->_attributes = [];
    }

    /**
     * Set attributes.
     */
    public function setAttributes($value)
    {
        foreach ($value as $key => $val) {
            $this->{$key} = $val;
        }
    }

    abstract public function primaryKey();
    /**
     * Get primary key.
     */
    abstract public function getPrimaryKey();

    /**
     *
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
     *
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
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Get interface.
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
