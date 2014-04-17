<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var __var_name_type__ __var_name_description__
     */
    public $name;
    /**
     * @var __var_version_type__ __var_version_description__
     */
    public $version = 1;

    /**
    * @inheritdoc
    **/
    public function getCollectorName()
    {
        return 'themes';
    }

    /**
    * @inheritdoc
    **/
    public function getModuleType()
    {
        return 'Theme';
    }

    /**
     * __method_getIdentityAssetBundle_description__
     */
    abstract public function getIdentityAssetBundle();

    /**
     * __method_getAssetBundles_description__
     * @return __return_getAssetBundles_type__ __return_getAssetBundles_description__
     */
    public function getAssetBundles()
    {
        return [$this->identityAssetBundle];
    }
}
