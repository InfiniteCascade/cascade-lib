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
 * DataSource [@doctodo write class description for DataSource].
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

    public $keys = ['id' => 'primaryKey'];

    /**
     * Get foreign data model.
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
     */
    public function getUnmappedForeignKeys()
    {
        $mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
        $u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
        unset($u[$this->foreignPrimaryKeyName]);

        return $u;
    }

    /**
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
