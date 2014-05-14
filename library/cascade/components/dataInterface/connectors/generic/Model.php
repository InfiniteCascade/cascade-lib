<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use Yii;
use yii\db\Query;

/**
 * Model [@doctodo write class description for Model]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Model extends \infinite\base\Object
{
    /**
     * @var __var_modelName_type__ __var_modelName_description__
     */
    public $modelName;
    /**
     * @var __var__interface_type__ __var__interface_description__
     */
    protected $_interface;
    /**
     * @var __var__attributes_type__ __var__attributes_description__
     */
    protected $_attributes;
    /**
     * @var __var__children_type__ __var__children_description__
     */
    protected $_children;

    /**
     * __method___clone_description__
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
     * Set interface
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * __method_reset_description__
     */
    public function reset()
    {
        $this->_attributes = [];
    }

    /**
     * Set attributes
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setAttributes($value)
    {
        foreach ($value as $key => $val) {
            $this->{$key} = $val;
        }
    }

    abstract public function primaryKey();
    /**
     * Get primary key
     * @return __return_getPrimaryKey_type__ __return_getPrimaryKey_description__
     */
    abstract public function getPrimaryKey();

    /**
     * __method_populateRecord_description__
     * @param __param_attributes_type__      $attributes __param_attributes_description__
     * @return __return_populateRecord_type__ __return_populateRecord_description__
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
     * __method_populateRecords_description__
     * @param __param_results_type__          $results __param_results_description__
     * @return __return_populateRecords_type__ __return_populateRecords_description__
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
     * Get attributes
     * @return __return_getAttributes_type__ __return_getAttributes_description__
     */
    public function getAttributes()
    {
        return $this->_attributes;
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
