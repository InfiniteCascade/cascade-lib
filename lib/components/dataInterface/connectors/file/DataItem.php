<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use cascade\components\dataInterface\RecursionException;

/**
 * DataItem data item for file data connectors.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataItem extends \cascade\components\dataInterface\connectors\generic\DataItem
{
    /**
     * @var Model the deferred model, without loaded attributes
     */
    public $deferredModel;
    /**
     * @inheritdoc
     */
    public function getId()
    {
        if ($this->isForeign) {
            if (isset($this->deferredModel)) {
                return $this->deferredModel->id;
            } elseif (isset($this->foreignObject)) {
                return $this->foreignObject->primaryKey;
            }
        } else {
            if (isset($this->localPrimaryKey)) {
                return $this->localPrimaryKey;
            } elseif (isset($this->localObject)) {
                return $this->localObject->primaryKey;
            }
        }
        if (isset($this->primaryObject)) {
            return $this->primaryObject->primaryKey;
        }

        return;
    }

    /**
     * fill absent relation attributes.
     *
     * @param array      $config      relationship configuration
     * @param Model $otherObject the other object to fill in the relation config data
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
     * Load the foreign object from the foreign data source.
     *
     * @throws RecursionException on recursive call when the object is already loading the same object
     */
    protected function loadForeignObject()
    {
        if ($this->_isLoadingForeignObject) {
            throw new RecursionException('Ran into recursion while loading foreign object');
        }
        $this->_isLoadingForeignObject = true;
        if (isset($this->deferredModel) && ($attributes = $this->deferredModel->attributes)) {
            $this->foreignObject = $this->dataSource->createModel($this->deferredModel->id, $this->deferredModel->attributes);
        }
        $this->_isLoadingForeignObject = false;
    }

    /**
     * Load the local object.
     *
     * @throws RecursionException on recursive call when the object is already loading the same object
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
