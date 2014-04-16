<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

abstract class Module extends \cascade\components\base\CollectorModule
{
    public $name;
    public $version = 1;

    public function getCollectorName()
    {
        return 'themes';
    }

    public function getModuleType()
    {
        return 'Theme';
    }

    abstract public function getIdentityAssetBundle();

    public function getAssetBundles()
    {
        return [$this->identityAssetBundle];
    }
}
