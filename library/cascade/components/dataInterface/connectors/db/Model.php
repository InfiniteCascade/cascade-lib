<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use Yii;
use yii\db\Query;

/**
 * Model [[@doctodo class_description:cascade\components\dataInterface\connectors\db\Model]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Model extends \cascade\components\dataInterface\connectors\generic\Model
{
    /**
     * @var [[@doctodo var_type:_tableName]] [[@doctodo var_description:_tableName]]
     */
    protected $_tableName;
    /**
     * @var [[@doctodo var_type:_meta]] [[@doctodo var_description:_meta]]
     */
    protected $_meta;
    /**
     * @var [[@doctodo var_type:_keys]] [[@doctodo var_description:_keys]]
     */
    protected $_keys;

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
     * @inheritdoc
     */
    public function hasAttribute($attribute)
    {
        return isset($this->meta) && is_object($this->meta) && $this->meta->hasAttribute($attribute);
    }
    /**
     * Set table name.
     */
    public function setTableName($value)
    {
        $this->_tableName = $value;
    }

    /**
     * Get children.
     *
     * @return [[@doctodo return_type:getChildren]] [[@doctodo return_description:getChildren]]
     */
    public function getChildren()
    {
        if (is_null($this->_children)) {
            $children = [];
            // for this application, there is no distinction between hasOne and hasMany on the database level
            $hasMany = array_merge($this->meta->hasMany, $this->meta->hasOne);
            foreach ($hasMany as $r) {
                if (!isset($r['foreignModel'])) {
                    var_dump($r);
                    exit;
                    continue;
                }
                if (is_string($r['foreignModel'])
                    && (!isset($this->interface->foreignModels[$r['foreignModel']])
                    || !($r['foreignModel'] = $this->interface->foreignModels[$r['foreignModel']]))) {
                    continue;
                }
                $params = isset($r['params']) ? $r['params'] : [];
                $params[':foreignKeyId'] = $this->primaryKey;

                $where = isset($r['where']) ? $r['where'] : [];
                if (!empty($where)) {
                    $where = ['and', $where, $r['foreignKey'] . '=:foreignKeyId'];
                } else {
                    $where = $r['foreignKey'] . '=:foreignKeyId';
                }
                $query = [
                    'where' => $where,
                    'params' => $params,
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
     * [[@doctodo method_description:primaryKey]].
     *
     * @return [[@doctodo return_type:primaryKey]] [[@doctodo return_description:primaryKey]]
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
     * Get primary key.
     *
     * @return [[@doctodo return_type:getPrimaryKey]] [[@doctodo return_description:getPrimaryKey]]
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
            return;
        }

        return $this->attributes[$pk];
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
     * Get meta.
     *
     * @return [[@doctodo return_type:getMeta]] [[@doctodo return_description:getMeta]]
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * Set meta.
     */
    public function setMeta($value)
    {
        $this->_meta = $value;
    }

    /**
     * [[@doctodo method_description:find]].
     *
     * @return [[@doctodo return_type:find]] [[@doctodo return_description:find]]
     */
    protected function find($params)
    {
        $debug = false;
        $q = new Query();
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
     * [[@doctodo method_description:findAll]].
     *
     * @param array $params [[@doctodo param_description:params]] [optional]
     *
     * @return [[@doctodo return_type:findAll]] [[@doctodo return_description:findAll]]
     */
    public function findAll($params = [])
    {
        $return = $this->populateRecords($this->find($params)->all($this->interface->db));

        return $return;
    }

    /**
     * [[@doctodo method_description:findOne]].
     *
     * @param array $params [[@doctodo param_description:params]] [optional]
     *
     * @return [[@doctodo return_type:findOne]] [[@doctodo return_description:findOne]]
     */
    public function findOne($params = [])
    {
        return $this->populateRecord($this->find($params)->one($this->interface->db));
    }

    /**
     * [[@doctodo method_description:findPrimaryKeys]].
     *
     * @param array $params [[@doctodo param_description:params]] [optional]
     *
     * @return [[@doctodo return_type:findPrimaryKeys]] [[@doctodo return_description:findPrimaryKeys]]
     */
    public function findPrimaryKeys($params = [])
    {
        $q = $this->find($params);
        $q->select($this->_tableName . '.' . $this->meta->schema->primaryKey[0]);

        return $q->column($this->interface->db);
    }

    /**
     * Get table name.
     *
     * @return [[@doctodo return_type:getTableName]] [[@doctodo return_description:getTableName]]
     */
    public function getTableName()
    {
        return $this->_tableName;
    }
}
