<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use infinite\helpers\ArrayHelper;
use Yii;

/**
 * DataSource [[@doctodo class_description:cascade\components\dataInterface\connectors\file\DataSource]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataSource extends \cascade\components\dataInterface\connectors\generic\DataSource
{
    /**
     * @inheritdoc
     */
    public $lazyForeign = false;
    /**
     * @inheritdoc
     */
    public $fieldMapClass = 'cascade\components\dataInterface\connectors\file\FieldMap';
    /**
     * @inheritdoc
     */
    public $dataItemClass = 'cascade\components\dataInterface\connectors\file\DataItem';
    /**
     * @inheritdoc
     */
    public $searchClass = 'cascade\components\dataInterface\connectors\file\Search';

    /**
     * @var [[@doctodo var_type:foreignModelClass]] [[@doctodo var_description:foreignModelClass]]
     */
    public $foreignModelClass = 'cascade\components\dataInterface\connectors\file\Model';

    /**
     * @var [[@doctodo var_type:_fileSource]] [[@doctodo var_description:_fileSource]]
     */
    protected $_fileSource;

    /**
     * Set file source.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setFileSource($value)
    {
        if (isset($this->module->sourceFiles[$value])) {
            $this->_fileSource = $this->module->sourceFiles[$value];
        } else {
            $this->_fileSource = false;
        }
    }

    /**
     * Get file source.
     *
     * @return [[@doctodo return_type:getFileSource]] [[@doctodo return_description:getFileSource]]
     */
    public function getFileSource()
    {
        return $this->_fileSource;
    }

    /**
     * Get foreign data model.
     *
     * @param [[@doctodo param_type:key]] $key [[@doctodo param_description:key]]
     *
     * @return [[@doctodo return_type:getForeignDataModel]] [[@doctodo return_description:getForeignDataModel]]
     */
    public function getForeignDataModel($key)
    {
        return false;
    }

    /**
     * Get unmapped foreign keys.
     *
     * @return [[@doctodo return_type:getUnmappedForeignKeys]] [[@doctodo return_description:getUnmappedForeignKeys]]
     */
    public function getUnmappedForeignKeys()
    {
        $mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
        $u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
        unset($u[$this->foreignPrimaryKeyName]);

        return $u;
    }

    /**
     * [[@doctodo method_description:loadForeignDataItems]].
     *
     * @return [[@doctodo return_type:loadForeignDataItems]] [[@doctodo return_description:loadForeignDataItems]]
     */
    protected function loadForeignDataItems()
    {
        if (!$this->fileSource) {
            \d("boom");

            return false;
        }
        $foreignModelClass = $this->foreignModelClass;

        $lines = $this->fileSource->getLines($this->lazyForeign);
        $lineCount = 0;
        foreach ($lines as $id => $line) {
            if ($this->lazyForeign) {
                $this->createForeignDataItem(null, ['deferredModel' => $line]);
            } else {
                $model = $this->createModel($line->id, $line->attributes);
                $this->createForeignDataItem($model, ['deferredModel' => $line]);
            }
            $lineCount++;
        }
        $this->task->addInfo("Processed {$lineCount} lines from {$this->fileSource->id}");
    }

    /**
     * [[@doctodo method_description:createModel]].
     *
     * @param [[@doctodo param_type:id]]         $id         [[@doctodo param_description:id]]
     * @param [[@doctodo param_type:attributes]] $attributes [[@doctodo param_description:attributes]]
     *
     * @return [[@doctodo return_type:createModel]] [[@doctodo return_description:createModel]]
     */
    public function createModel($id, $attributes)
    {
        return Yii::createObject(['class' => $this->foreignModelClass, 'tableName' => $this->fileSource->id, 'interface' => $this->module, 'id' => $id, 'attributes' => $attributes]);
    }
}
