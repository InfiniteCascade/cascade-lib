<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use Yii;
use yii\db\Query;

class Model extends \cascade\components\dataInterface\connectors\generic\Model
{
    protected $_id;
    /**
     * @var __var__keys_type__ __var__keys_description__
     */
    protected $_keys;
    /**
     * @var __var__children_type__ __var__children_description__
     */
    protected $_children = [];

    // public $attributeNames = [];

    // public function hasAttribute($attribute)
    // {
    //     return in_array($attribute, $this->attributeNames);
    // }
    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        return $this->_id = $id;
    }

    /**
     * Get children
     * @return __return_getChildren_type__ __return_getChildren_description__
     */
    public function getChildren()
    {
        return $this->_children;
    }

    public function addChild($object)
    {
        $this->_children[$object->id] = $object;
    }

    /**
     * __method_primaryKey_description__
     * @return __return_primaryKey_type__ __return_primaryKey_description__
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Get primary key
     * @return __return_getPrimaryKey_type__ __return_getPrimaryKey_description__
     */
    public function getPrimaryKey()
    {
        return $this->id;
    }



    /**
     * Get attributes
     * @return __return_getAttributes_type__ __return_getAttributes_description__
     */
    public function getAttributes()
    {
        \d("boom!");exit;
        $a = [];
        foreach ($this->meta->attributeKeys as $k) {
            $a[$k] = null;
            if (is_array($this->_attributes) && isset($this->_attributes[$k])) {
                $a[$k] = $this->_attributes[$k];
            }
        }

        return $a;
    }

    public function setTableName($value)
    {
        $this->_tableName = $value;
    }

    public function getTableName()
    {
        if (isset($this->_tableName)) {
            return $this->_tableName;
        }
        return static::baseClassName();
    }
}
