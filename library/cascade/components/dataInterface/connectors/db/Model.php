<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use Yii;
use yii\db\Query;

/**
 * Model [@doctodo write class description for Model]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Model extends \infinite\base\Object
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
     * @var __var__tableName_type__ __var__tableName_description__
     */
    protected $_tableName;
    /**
     * @var __var__meta_type__ __var__meta_description__
     */
    protected $_meta;
    /**
     * @var __var__attributes_type__ __var__attributes_description__
     */
    protected $_attributes;
    /**
     * @var __var__keys_type__ __var__keys_description__
     */
    protected $_keys;
    /**
     * @var __var__children_type__ __var__children_description__
     */
    protected $_children;

    /**
    * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $metaConfig = [];
        if (isset($this->_meta)) {
            $metaConfig = $this->_meta;
        }
        $this->_meta = Meta::get($this->interface, $this->tableName);
        Yii::configure($this->_meta, $metaConfig);
    }

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
        if (isset($this->meta) && is_object($this->meta) && $this->meta->hasAttribute($name)) {
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
        if (isset($this->meta) && $this->meta->hasAttribute($name) && isset($this->_attributes[$name])) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
    * @inheritdoc
     */
    public function __unset($name)
    {
        if (isset($this->meta) && $this->meta->hasAttribute($name)) {
            unset($this->_attributes[$name]);

            return true;
        }

        return parent::__unset($name);
    }

    /**
     * Set table name
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setTableName($value)
    {
        $this->_tableName = $value;
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

    /**
     * Get children
     * @return __return_getChildren_type__ __return_getChildren_description__
     */
    public function getChildren()
    {
        if (is_null($this->_children)) {
            $children = [];
            // for this application, there is no distinction between hasOne and hasMany on the database level
            $hasMany = array_merge($this->meta->hasMany, $this->meta->hasOne);
            foreach ($hasMany as $r) {
                if (!isset($r['foreignModel'])) { var_dump($r);exit; continue; }
                if (is_string($r['foreignModel'])
                    && (!isset($this->interface->foreignModels[$r['foreignModel']])
                    || !($r['foreignModel'] = $this->interface->foreignModels[$r['foreignModel']]))) {
                    continue;
                }
                $params = isset($r['params']) ? $r['params'] : [];
                $params[':foreignKeyId'] = $this->primaryKey;

                $where = isset($r['where']) ? $r['where'] : [];
                if (!empty($where)) {
                    $where = ['and', $where, $r['foreignKey'] .'=:foreignKeyId'];
                } else {
                    $where = $r['foreignKey'] .'=:foreignKeyId';
                }
                $query = [
                    'where' => $where,
                    'params' => $params
                ];
                if (isset($r['join'])) {
                    $query['join'] = $r['join'];
                }
                $children[$r['foreignModel']->tableName] = $r['foreignModel']->findPrimaryKeys($query);
            }

            $this->_children = $children;
        }

        return $this->_children;
    }
    /**
     * __method_primaryKey_description__
     * @return __return_primaryKey_type__ __return_primaryKey_description__
     */
    public function primaryKey()
    {
        $pk = $this->meta->schema->primaryKey;
        if (is_array($pk)) {
            $ppk = [];
            foreach ($pk as $key) {
                $ppk[] = $key;
            }

            return implode('.', $ppk);
        }

        return $pk;
    }

    /**
     * Get primary key
     * @return __return_getPrimaryKey_type__ __return_getPrimaryKey_description__
     */
    public function getPrimaryKey()
    {
        $pk = $this->meta->schema->primaryKey;
        if (is_array($pk)) {
            $ppk = [];
            foreach ($pk as $key) {
                if (!isset($this->attributes[$key])) {
                    $ppk[] = null;
                } else {
                    $ppk[] = $this->attributes[$key];
                }
            }

            return implode('.', $ppk);
        }
        if (!isset($this->attributes[$pk])) {
            return null;
        }

        return $this->attributes[$pk];
    }

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
     * Get meta
     * @return __return_getMeta_type__ __return_getMeta_description__
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * Set meta
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setMeta($value)
    {
        $this->_meta = $value;
    }

    /**
     * Get interface
     * @return __return_getInterface_type__ __return_getInterface_description__
     */
    public function getInterface()
    {
        return $this->_interface;
    }

    /**
     * __method_find_description__
     * @param __param_params_type__ $params __param_params_description__
     * @return __return_find_type__  __return_find_description__
     */
    public function find($params)
    {
        $debug = false;
        $q = new Query;
        $q->select('*');
        $q->from($this->_tableName);
        foreach ($params as $k => $v) {
            if ($k === 'join') {
                foreach ($v as $join) {
                    if (!isset($join['type'])) {
                        $join['type'] = 'INNER JOIN';
                    }
                    if (!isset($join['params'])) {
                        $join['params'] = [];
                    }
                    $q->join($join['type'], $join['table'], $join['on'], $join['params']);
                }
                $debug = true;
            } elseif (in_array($k, ['where'])) {
                $q->{$k}($v);
            } else {
                $q->{$k} = $v;
            }
        }
        if ($debug) {
            //var_dump($q->createCommand()->rawSql);exit;
        }

        return $q;
    }

    /**
     * __method_findAll_description__
     * @param array                   $params __param_params_description__ [optional]
     * @return __return_findAll_type__ __return_findAll_description__
     */
    public function findAll($params = [])
    {
        $return = $this->populateRecords($this->find($params)->all($this->interface->db));

        return $return;
    }

    /**
     * __method_findOne_description__
     * @param array                   $params __param_params_description__ [optional]
     * @return __return_findOne_type__ __return_findOne_description__
     */
    public function findOne($params = [])
    {
        return $this->populateRecord($this->find($params)->one($this->interface->db));
    }

    /**
     * __method_findPrimaryKeys_description__
     * @param array                           $params __param_params_description__ [optional]
     * @return __return_findPrimaryKeys_type__ __return_findPrimaryKeys_description__
     */
    public function findPrimaryKeys($params = [])
    {
        $q = $this->find($params);
        $q->select($this->_tableName .'.'. $this->meta->schema->primaryKey[0]);

        return $q->column($this->interface->db);
    }

    /**
     * Get table name
     * @return __return_getTableName_type__ __return_getTableName_description__
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

}
