<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\dataInterface;

use teal\action\Action as BaseAction;

/**
 * Module [[@doctodo class_description:cascade\components\dataInterface\Module]].
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
        return 'dataInterfaces';
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Interface';
    }

    /**
     * [[@doctodo method_description:run]].
     *
     * @param teal\action\Action $action [[@doctodo param_description:action]]
     */
    abstract public function run(BaseAction $action);
}
