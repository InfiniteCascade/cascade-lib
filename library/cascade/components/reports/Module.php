<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\reports;

/**
 * Module [@doctodo write class description for Module].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     */
    public $name;
    /**
     */
    public $version = 1;

    public $icon = 'fa fa-filter';

    /**
     * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'reports';
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Report';
    }
}
