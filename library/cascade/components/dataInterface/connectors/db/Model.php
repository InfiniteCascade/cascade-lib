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
 * Model for foreign data items in a database connection.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Model extends \cascade\components\dataInterface\connectors\generic\Model
{
    /**
     * @var string table name for model
     */
    protected $_tableName;
    /**
     * @var Meta meta object
     */
    protected $_meta;
    /**
     * @var array keys from model
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
     *
     * @param string $value the table name
     */
    public function setTableName($value)
    {
        $this->_tableName = $value;
    }

    /**
     * Get children.
     *
     * @return array child objects for this model
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
     * Retrieve the primary key column for the model item.
     *
     * @return string the primary key
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
     * @return string the primary key
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
     * @return array of the attribute values
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
     * @return Meta the model meta
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * Set meta.
     *
     * @param Meta $value the meta object
     */
    public function setMeta($value)
    {
        $this->_meta = $value;
    }

    /**
     * Find the models for a foreign data source.
     *
     * @param array $params the query parameters
     *
     * @return Query the foreign data query
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
     * Find all the foreign database records.
     *
     * @param array $params find params for the query [optional]
     *
     * @return array of foreign data records
     */
    public function findAll($params = [])
    {
        $return = $this->populateRecords($this->find($params)->all($this->interface->db));

        return $return;
    }

    /**
     * Find one foreign database record.
     *
     * @param array $params find params for the query [optional]
     *
     * @return Model database record
     */
    public function findOne($params = [])
    {
        return $this->populateRecord($this->find($params)->one($this->interface->db));
    }

    /**
     * Return the primary keys (used in lazy loading).
     *
     * @param array $params find params for the query [optional]
     *
     * @return array keys from the data source items
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
     * @return string name of the table
     */
    public function getTableName()
    {
        return $this->_tableName;
    }
}
