<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

/**
 * Collector [@doctodo write class description for Collector].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \infinite\base\collector\Module
{
    /**
     */
    protected $_initialItems = [];

    /**
     * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return Item::className();
    }

    /**
     * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Storage';
    }

    /**
     * @inheritdoc
     */
    public function getInitialItems()
    {
        return $this->_initialItems;
    }

    /**
     * Set initial items.
     */
    public function setInitialItems($value)
    {
        $this->_initialItems = $value;
    }
}
