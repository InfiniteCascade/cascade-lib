<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Module extends \cascade\components\base\CollectorModule
{
    public $name;
    public $version = 1;

    /**
    * @inheritdoc
    **/
    public function getCollectorName()
    {
        return 'storageHandlers';
    }

    /**
    * @inheritdoc
    **/
    public function getModuleType()
    {
        return 'Storage';
    }
}
