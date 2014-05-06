<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use cascade\components\dataInterface\RecursionException;
use cascade\components\dataInterface\MissingItemException;

/**
 * DataItem [@doctodo write class description for DataItem]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataItem extends \cascade\components\dataInterface\DataItem
{
    /**
     * @var __var__isLoadingForeignObject_type__ __var__isLoadingForeignObject_description__
     */
    protected $_isLoadingForeignObject = false;
    /**
     * @var __var__isLoadingLocalObject_type__ __var__isLoadingLocalObject_description__
     */
    protected $_isLoadingLocalObject = false;

    /**
    * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_LOAD_FOREIGN_OBJECT, [$this, 'loadForeignObject']);
        $this->on(self::EVENT_LOAD_LOCAL_OBJECT, [$this, 'loadLocalObject']);
        parent::init();
    }

    /**
     * __method_loadForeignObject_description__
     * @throws RecursionException __exception_RecursionException_description__
     * @throws MissingItemException __exception_MissingItemException_description__
     */
    abstract protected function loadForeignObject();

    /**
     * __method_loadLocalObject_description__
     * @throws RecursionException __exception_RecursionException_description__
     */
    abstract protected function loadLocalObject();
}
