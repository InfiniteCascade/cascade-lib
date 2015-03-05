<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

/**
 * Module [[@doctodo class_description:cascade\components\web\themes\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var [[@doctodo var_type:name]] [[@doctodo var_description:name]]
     */
    public $name;
    /**
     * @var [[@doctodo var_type:version]] [[@doctodo var_description:version]]
     */
    public $version = 1;

    /**
     * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'themes';
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Theme';
    }

    /**
     * Get identity asset bundle.
     */
    abstract public function getIdentityAssetBundle();

    /**
     * Get asset bundles.
     *
     * @return [[@doctodo return_type:getAssetBundles]] [[@doctodo return_description:getAssetBundles]]
     */
    public function getAssetBundles()
    {
        return [$this->identityAssetBundle];
    }
}
