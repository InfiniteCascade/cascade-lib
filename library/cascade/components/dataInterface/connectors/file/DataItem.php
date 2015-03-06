<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use cascade\components\dataInterface\RecursionException;

/**
 * DataItem [[@doctodo class_description:cascade\components\dataInterface\connectors\file\DataItem]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataItem extends \cascade\components\dataInterface\connectors\generic\DataItem
{
    /**
     * @var [[@doctodo var_type:deferredModel]] [[@doctodo var_description:deferredModel]]
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
     * [[@doctodo method_description:fillRelationConfig]].
     *
     * @param [[@doctodo param_type:config]]      $config      [[@doctodo param_description:config]]
     * @param [[@doctodo param_type:otherObject]] $otherObject [[@doctodo param_description:otherObject]]
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
     * [[@doctodo method_description:loadForeignObject]].
     *
     * @throws RecursionException [[@doctodo exception_description:RecursionException]]
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
     * [[@doctodo method_description:loadLocalObject]].
     *
     * @throws RecursionException [[@doctodo exception_description:RecursionException]]
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
