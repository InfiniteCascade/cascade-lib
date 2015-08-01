<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\storageHandlers;

/**
 * Collector [[@doctodo class_description:cascade\components\storageHandlers\Collector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \canis\base\collector\Module
{
    /**
     * @var [[@doctodo var_type:_initialItems]] [[@doctodo var_description:_initialItems]]
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
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setInitialItems($value)
    {
        $this->_initialItems = $value;
    }
}
