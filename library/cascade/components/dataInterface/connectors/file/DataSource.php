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
 * DataSource [@doctodo write class description for DataSource].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataSource extends \cascade\components\dataInterface\connectors\generic\DataSource
{
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

    public $foreignModelClass = 'cascade\components\dataInterface\connectors\file\Model';

    protected $_fileSource;

    public function setFileSource($value)
    {
        if (isset($this->module->sourceFiles[$value])) {
            $this->_fileSource = $this->module->sourceFiles[$value];
        } else {
            $this->_fileSource = false;
        }
    }

    public function getFileSource()
    {
        return $this->_fileSource;
    }

    /**
     * Get foreign data model.
     *
     * @param __param_key_type__ $key __param_key_description__
     *
     * @return __return_getForeignDataModel_type__ __return_getForeignDataModel_description__
     */
    public function getForeignDataModel($key)
    {
        return false;
    }

    /**
     * Get unmapped foreign keys.
     *
     * @return __return_getUnmappedForeignKeys_type__ __return_getUnmappedForeignKeys_description__
     */
    public function getUnmappedForeignKeys()
    {
        $mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
        $u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
        unset($u[$this->foreignPrimaryKeyName]);

        return $u;
    }

    /**
     * __method_loadForeignDataItems_description__.
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

    public function createModel($id, $attributes)
    {
        return Yii::createObject(['class' => $this->foreignModelClass, 'tableName' => $this->fileSource->id, 'interface' => $this->module, 'id' => $id, 'attributes' => $attributes]);
    }
}
