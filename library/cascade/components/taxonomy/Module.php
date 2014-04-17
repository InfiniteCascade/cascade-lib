<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\taxonomy;

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
     * @var __var_icon_type__ __var_icon_description__
     */
    public $icon = 'ic-icon-info';
    /**
     * @var __var_priority_type__ __var_priority_description__
     */
    public $priority = 1000;
    /**
     * @var __var_version_type__ __var_version_description__
     */
    public $version = 1;

    /**
    * @inheritdoc
    **/
    public function getCollectorName()
    {
        return 'types';
    }

    /**
     * Get settings
     */
    abstract public function getSettings();
}
