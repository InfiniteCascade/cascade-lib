<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\reports;

/**
 * Module [[@doctodo class_description:cascade\components\reports\Module]].
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
     * @var [[@doctodo var_type:icon]] [[@doctodo var_description:icon]]
     */
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

    /**
     * Returns a short description for the report
     */
    abstract public function getDescription();

    /**
     * Returns a the object types associated with this object
     */
    public function getObjectTypes()
    {
        return [];
    }

    public function getVariations()
    {
        return false;
    }
}
