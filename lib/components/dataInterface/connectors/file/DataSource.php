<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use canis\helpers\ArrayHelper;
use Yii;

/**
 * DataSource data source for the file connectors.
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
     * @var string class name for foreign models
     */
    public $foreignModelClass = 'cascade\components\dataInterface\connectors\file\Model';

    /**
     * @var Source the file source for this connector
     */
    protected $_fileSource;

    /**
     * Set file source.
     *
     * @param Source|bool $value file source or false if not found
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
     * @return Source|bool the file source object or false if not found
     */
    public function getFileSource()
    {
        return $this->_fileSource;
    }

    /**
     * Get foreign data model.
     *
     * @param int|string $key the foreign model's primary key
     *
     * @return Model|false the foreign model or false if not found
     */
    public function getForeignDataModel($key)
    {
        return false;
    }

    /**
     * Get unmapped foreign keys.
     *
     * @return array the foreign keys that haven't been mapped
     */
    public function getUnmappedForeignKeys()
    {
        $mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
        $u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
        unset($u[$this->foreignPrimaryKeyName]);

        return $u;
    }

    /**
     * Load the data items from the file source.
     *
     * @return bool|null False on fail, null on success (@todo change this to true)
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
     * Create the model.
     *
     * @param string|int         $id         the primary key for the model
     * @param array $attributes attrribute for the model
     *
     * @return Model the foreign model
     */
    public function createModel($id, $attributes)
    {
        return Yii::createObject(['class' => $this->foreignModelClass, 'tableName' => $this->fileSource->id, 'interface' => $this->module, 'id' => $id, 'attributes' => $attributes]);
    }
}
