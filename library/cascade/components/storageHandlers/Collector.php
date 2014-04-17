<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

/**
 * Collector [@doctodo write class description for Collector]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Collector extends \infinite\base\collector\Module
{
    /**
     * @var __var__initialItems_type__ __var__initialItems_description__
     */
    protected $_initialItems = [];

    /**
    * @inheritdoc
    **/
    public function getCollectorItemClass()
    {
        return 'cascade\\components\\storageHandlers\\Item';
    }

    /**
    * @inheritdoc
    **/
    public function getModulePrefix()
    {
        return 'Storage';
    }

    /**
    * @inheritdoc
    **/
    public function getInitialItems()
    {
        return $this->_initialItems;
    }

    /**
     * __method_setInitialItems_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setInitialItems($value)
    {
        $this->_initialItems = $value;
    }
}
