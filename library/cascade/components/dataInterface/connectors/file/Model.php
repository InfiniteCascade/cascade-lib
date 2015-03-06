<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

/**
 * Model [[@doctodo class_description:cascade\components\dataInterface\connectors\file\Model]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Model extends \cascade\components\dataInterface\connectors\generic\Model
{
    /**
     * @var [[@doctodo var_type:_id]] [[@doctodo var_description:_id]]
     */
    protected $_id;
    /**
     * @var [[@doctodo var_type:_keys]] [[@doctodo var_description:_keys]]
     */
    protected $_keys;
    /**
     * @var [[@doctodo var_type:_meta]] [[@doctodo var_description:_meta]]
     */
    protected $_meta;
    /**
     * @var [[@doctodo var_type:_children]] [[@doctodo var_description:_children]]
     */
    protected $_children = [];

    // public $attributeNames = [];

    // public function hasAttribute($attribute)
    // {
    //     return in_array($attribute, $this->attributeNames);
    // }
    /**
     * Get id.
     *
     * @return [[@doctodo return_type:getId]] [[@doctodo return_description:getId]]
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set id.
     *
     * @param [[@doctodo param_type:id]] $id [[@doctodo param_description:id]]
     *
     * @return [[@doctodo return_type:setId]] [[@doctodo return_description:setId]]
     */
    public function setId($id)
    {
        return $this->_id = $id;
    }

    /**
     * Set source file.
     *
     * @param [[@doctodo param_type:sourceFile]] $sourceFile [[@doctodo param_description:sourceFile]]
     *
     * @return [[@doctodo return_type:setSourceFile]] [[@doctodo return_description:setSourceFile]]
     */
    public function setSourceFile($sourceFile)
    {
        $this->_meta = Meta::get($this->interface, $sourceFile);

        return $this;
    }

    /**
     * Get children.
     *
     * @return [[@doctodo return_type:getChildren]] [[@doctodo return_description:getChildren]]
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * [[@doctodo method_description:addChild]].
     *
     * @param [[@doctodo param_type:object]] $object [[@doctodo param_description:object]]
     */
    public function addChild($object)
    {
        $this->_children[$object->id] = $object;
    }

    /**
     * [[@doctodo method_description:primaryKey]].
     *
     * @return [[@doctodo return_type:primaryKey]] [[@doctodo return_description:primaryKey]]
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Get primary key.
     *
     * @return [[@doctodo return_type:getPrimaryKey]] [[@doctodo return_description:getPrimaryKey]]
     */
    public function getPrimaryKey()
    {
        return $this->id;
    }

    /**
     * Get attributes.
     *
     * @return [[@doctodo return_type:getAttributes]] [[@doctodo return_description:getAttributes]]
     */
    public function getAttributes()
    {
        $a = [];
        foreach ($this->meta->attributeKeys as $k) {
            $a[$k] = null;
            if (is_array($this->_attributes) && isset($this->_attributes[$k])) {
                $a[$k] = $this->_attributes[$k];
            }
        }

        return $a;
    }

    /**
     * Set table name.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setTableName($value)
    {
        $this->_tableName = $value;
    }

    /**
     * @inheritdoc
     */
    public function getTableName()
    {
        if (isset($this->_tableName)) {
            return $this->_tableName;
        }

        return static::baseClassName();
    }

    /**
     * [[@doctodo method_description:fetchAll]].
     *
     * @param boolean $lazy [[@doctodo param_description:lazy]] [optional]
     *
     * @return [[@doctodo return_type:fetchAll]] [[@doctodo return_description:fetchAll]]
     */
    public static function fetchAll($lazy = false)
    {
        $models = [];
        $baseModel = new static();
        foreach ($baseModel->meta->sourceFile->getLines($lazy, true) as $line) {
            $model = $this->populateRecord($line->attributes);
            $models[] = $model;
        }

        return $models;
    }
}
