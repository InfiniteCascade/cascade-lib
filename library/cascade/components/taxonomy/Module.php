<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\taxonomy;

abstract class Module extends \cascade\components\base\CollectorModule
{
    public $name;
    public $icon = 'ic-icon-info';
    public $priority = 1000;
    public $version = 1;

    public function getCollectorName()
    {
        return 'types';
    }

    abstract public function getSettings();
}
