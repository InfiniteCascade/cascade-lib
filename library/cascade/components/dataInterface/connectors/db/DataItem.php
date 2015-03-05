<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use cascade\components\dataInterface\MissingItemException;
use cascade\components\dataInterface\RecursionException;

/**
 * DataItem [[@doctodo class_description:cascade\components\dataInterface\connectors\db\DataItem]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataItem extends \cascade\components\dataInterface\connectors\generic\DataItem
{
    /**
     * [[@doctodo method_description:fillRelationConfig]].
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
     * @throws RecursionException   [[@doctodo exception_description:RecursionException]]
     * @throws MissingItemException [[@doctodo exception_description:MissingItemException]]
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
            throw new MissingItemException('Foreign item could not be found: ' . $this->foreignPrimaryKey);
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
