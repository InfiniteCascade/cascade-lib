<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\tools;

/**
 * Module [@doctodo write class description for Module].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
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

    public $icon = 'fa fa-wrench';

    /**
     * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'tools';
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Tool';
    }
}
