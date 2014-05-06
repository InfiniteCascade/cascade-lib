<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use cascade\components\dataInterface\RecursionException;
use cascade\components\dataInterface\MissingItemException;

/**
 * DataItem [@doctodo write class description for DataItem]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataItem extends \cascade\components\dataInterface\connectors\generic\DataItem
{


    /**
    * @inheritdoc
     */
    protected function handleForeign($baseAttributes = [])
    {
        if ($this->ignoreForeignObject) {
            return null;
        }

        // foreign to local

        // find or start up local object
        $localModel = $this->dataSource->localModel;

        if (!isset($this->localObject)) {
            $this->localObject = new $localModel;
        }

        $this->localObject->auditAgent = $this->module->collectorItem->interfaceObject->primaryKey;

        $attributes = $this->dataSource->buildLocalAttributes($this->foreignObject, $this->localObject);
        if (empty($attributes)) {
            return false;
        }

        $relations = [];
        // load local object
        foreach ($attributes as $key => $value) {
            $this->localObject->{$key} = $value;
        }

        foreach (array_merge($this->dataSource->baseAttributes, $baseAttributes) as $key => $value) {
            $this->localObject->{$key} = $value;
        }

        // save local object
        if (!$this->localObject->save()) {
            return false;
        }

        // save foreign key map
        if (!$this->dataSource->saveKeyTranslation($this->foreignObject, $this->localObject)) {
            throw new \Exception("Unable to save key translation!");
        }

        // loop through children
        foreach ($this->foreignObject->children as $table => $children) {
            $dataSource = $this->module->getDataSource($table);
            if (empty($dataSource) || !$dataSource->isReady()) { continue; }
            foreach ($children as $childId) {
                // let the handler figure it out
                if (!($dataItem = $dataSource->getForeignDataItem($childId))) {
                    continue;
                }
                $childLocalObject = $dataItem->handle(true, ['indirectObject' => $this->localObject, 'relationModels' => [['parent_object_id' => $this->localObject->primaryKey]]]);
            }
        }

        return $this->localObject;
    }

    /**
     * __method_fillRelationConfig_description__
     * @param __param_config_type__      $config      __param_config_description__
     * @param __param_otherObject_type__ $otherObject __param_otherObject_description__
     */
    protected function fillRelationConfig(&$config, $otherObject)
    {
        if (isset($config['parent_object_id'])) {
            $config['child_object_id'] = $otherObject;
        } elseif (isset($config['child_object_id'])) {
            $config['parent_object_id'] = $otherObject;
        }
    }

    /**
    * @inheritdoc
     */
    protected function handleLocal($baseAttributes = [])
    {
        if ($this->ignoreLocalObject) {
            return false;
        }
        // local to foreign

        // find
        return false;
    }

    /**
     * __method_loadForeignObject_description__
     * @throws RecursionException __exception_RecursionException_description__
     * @throws MissingItemException __exception_MissingItemException_description__
     */
    protected function loadForeignObject()
    {
        if ($this->_isLoadingForeignObject) {
            throw new RecursionException('Ran into recursion while loading foreign object');
        }
        $this->_isLoadingForeignObject = true;
        if (isset($this->foreignPrimaryKey)) {
            $foreignObject = $this->dataSource->getForeignDataModel($this->foreignPrimaryKey);
            if ($foreignObject) {
                $this->foreignObject = $foreignObject;
            }
        }
        if (empty($this->_foreignObject)) {
            \d($this->foreignPrimaryKey);
            \d($this->dataSource->name);
            throw new MissingItemException('Foreign item could not be found: '. $this->foreignPrimaryKey);
        }
        $this->_isLoadingForeignObject = false;
    }

    /**
     * __method_loadLocalObject_description__
     * @throws RecursionException __exception_RecursionException_description__
     */
    protected function loadLocalObject()
    {
        if ($this->_isLoadingLocalObject) {
            throw new RecursionException('Ran into recursion while loading local object');
        }
        $this->_isLoadingLocalObject = true;
        if (isset($this->foreignObject) && !isset($this->_localObject)) {
            $keyTranslation = $this->dataSource->getKeyTranslation($this->foreignObject);
            if (!empty($keyTranslation) && ($localObject = $keyTranslation->object)) {
                $this->localObject = $localObject;
            }
        }
        $this->_isLoadingLocalObject = false;
    }
}
