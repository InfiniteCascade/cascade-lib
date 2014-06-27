<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\commands;

use Yii;

use infinite\helpers\Console;
use yii\console\Exception;

use infinite\helpers\ArrayHelper;

ini_set('memory_limit', -1);

/**
 * InterfaceController [@doctodo write class description for InterfaceController]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class InterfaceController extends \infinite\console\Controller
{
    /**
     * @var __var__interface_type__ __var__interface_description__
     */
    protected $_interface;
    /**
     * @var __var_verbose_type__ __var_verbose_description__
     */
    public $verbose;
    /**
     * @var __var__started_type__ __var__started_description__
     */
    protected $_started = false;


    /**
     * __method_start_description__
     */
    public function runAction($id, $params = [])
    {
        $this->_started = true;
        return parent::runAction($id, $params);
    }

    /**
     * __method_actionIndex_description__
     */
    public function actionIndex()
    {
        $this->actionRunOne();
    }

    /**
     * __method_actionRunOne_description__
     */
    public function actionRunOne()
    {
        $this->out("Run Interface ". $this->interface->object->name, Console::UNDERLINE, Console::FG_GREEN);
        $this->hr();
        $this->interface->run();
    }

    /**
     * Get interface
     * @return __return_getInterface_type__ __return_getInterface_description__
     */
    public function getInterface()
    {
        if ($this->_started && is_null($this->_interface)) {
            $interfaces = ArrayHelper::map(Yii::$app->collectors['dataInterfaces']->getAll(), 'systemId', 'object.name');
            $this->interface = $this->select("Choose interface", $interfaces);
        }
        return $this->_interface;
    }

    /**
     * Set interface
     * @param __param_value_type__ $value __param_value_description__
     * @throws Exception __exception_Exception_description__
     */
    public function setInterface($value)
    {
        if (($interfaceItem = Yii::$app->collectors['dataInterfaces']->getOne($value)) && ($interface = $interfaceItem->object)) {
            $this->_interface = $interfaceItem;
        } else {
            throw new Exception("Invalid interface!");
        }
    }

    /**
    * @inheritdoc
     */
    public function options($id)
    {
        return array_merge(parent::options($id), ['interface', 'verbose']);
    }
}
