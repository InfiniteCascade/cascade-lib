<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use cascade\components\dataInterface\MissingItemException;
use cascade\components\dataInterface\RecursionException;

/**
 * DataItem [@doctodo write class description for DataItem].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataItem extends \cascade\components\dataInterface\connectors\generic\DataItem
{
    public $deferredModel;
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
     * __method_fillRelationConfig_description__.
     *
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
     * __method_loadForeignObject_description__.
     *
     * @throws RecursionException   __exception_RecursionException_description__
     * @throws MissingItemException __exception_MissingItemException_description__
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
     * __method_loadLocalObject_description__.
     *
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
