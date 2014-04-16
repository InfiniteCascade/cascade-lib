<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\commands;

use Yii;

use yii\helpers\Console;
use yii\console\Exception;

use infinite\helpers\ArrayHelper;

ini_set('memory_limit', -1);

class InterfaceController extends \infinite\console\Controller
{
    protected $_interface;
    public $verbose;
    protected $_started = false;

    public function events()
    {
        return [
            self::EVENT_BEFORE_ACTION => [$this, 'start']
        ];
    }

    public function start()
    {
        $this->_started = true;
    }

    public function actionIndex()
    {
        $this->actionRunOne();
    }

    public function actionRunOne()
    {
        $this->out("Run Interface ". $this->interface->object->name, Console::UNDERLINE, Console::FG_GREEN);
        $this->hr();
        $this->interface->run();
    }

    public function getInterface()
    {
        if ($this->_started && is_null($this->_interface)) {
            $interfaces = ArrayHelper::map(Yii::$app->collectors['dataInterfaces']->getAll(), 'systemId', 'object.name');
            $this->interface = $this->select("Choose interface", $interfaces);
        }

        return $this->_interface;
    }

    public function setInterface($value)
    {
        if (($interfaceItem = Yii::$app->collectors['dataInterfaces']->getOne($value)) && ($interface = $interfaceItem->object)) {
            $this->_interface = $interfaceItem;
        } else {
            throw new Exception("Invalid interface!");
        }
    }

    public function options($id)
    {
        return array_merge(parent::options($id), ['interface', 'verbose']);
    }
}
