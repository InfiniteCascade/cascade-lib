<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use infinite\helpers\ArrayHelper;

/**
 * DataSource data source for database data source connectors.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataSource extends \cascade\components\dataInterface\connectors\generic\DataSource
{
    /**
     * @inheritdoc
     */
    public $fieldMapClass = 'cascade\components\dataInterface\connectors\db\FieldMap';
    /**
     * @inheritdoc
     */
    public $dataItemClass = 'cascade\components\dataInterface\connectors\db\DataItem';
    /**
     * @inheritdoc
     */
    public $searchClass = 'cascade\components\dataInterface\connectors\db\Search';

    /**
     * @inheritdoc
     */
    public $keys = ['id' => 'primaryKey'];

    /**
     * Get foreign data model.
     *
     * @param [[@doctodo param_type:key]] $key [[@doctodo param_description:key]]
     *
     * @return Model foreign data model
     */
    public function getForeignDataModel($key)
    {
        $config = $this->settings['foreignPullParams'];
        if (!isset($config['where'])) {
            $config['where'] = [];
        }
        if (!empty($config['where'])) {
            $config['where'] = ['and', $config['where'], [$this->foreignModel->primaryKey() => $key]];
        } else {
            $config['where'][$this->foreignModel->primaryKey()] = $key;
        }
        //var_dump($this->foreignModel->find($config)->count('*', $this->module->db));
        return $this->foreignModel->findOne($config);
    }

    /**
     * Get unmapped foreign keys.
     *
     * @return array array without the mapped foreign keys
     */
    public function getUnmappedForeignKeys()
    {
        $mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
        $u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
        unset($u[$this->foreignPrimaryKeyName]);

        return $u;
    }

    /**
     * Load the foreign data items, either lazily or not.
     */
    protected function loadForeignDataItems()
    {
        $this->_foreignDataItems = [];
        if ($this->lazyForeign) {
            $primaryKeys = $this->foreignModel->findPrimaryKeys($this->settings['foreignPullParams']);
            foreach ($primaryKeys as $primaryKey) {
                $this->createForeignDataItem(null, ['foreignPrimaryKey' => $primaryKey]);
            }
        } else {
            $foreignModels = $this->foreignModel->findAll($this->settings['foreignPullParams']);
            foreach ($foreignModels as $key => $model) {
                $this->createForeignDataItem($model, []);
            }
        }
    }
}
