<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\commands;

use cascade\components\dataInterface\ConsoleAction;
use infinite\helpers\ArrayHelper;
use infinite\helpers\Console;
use Yii;
use yii\console\Exception;

ini_set('memory_limit', -1);

/**
 * InterfaceController [@doctodo write class description for InterfaceController].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class InterfaceController extends \infinite\console\Controller
{
    /**
     */
    protected $_interface;
    /**
     */
    public $verbose;
    /**
     */
    public function actionIndex()
    {
        $this->actionRunOne();
    }

    /**
     */
    public function actionRunOne()
    {
        $this->out("Run Interface " . $this->dataInterface->object->name, Console::UNDERLINE, Console::FG_GREEN);
        $this->hr();
        $this->dataInterface->run(null, new ConsoleAction());
    }

    /**
     * Get interface.
     */
    public function getDataInterface()
    {
        if (!$this->started) {
            return $this->_interface;
        }
        if (is_null($this->_interface)) {
            $interfaces = ArrayHelper::map(Yii::$app->collectors['dataInterfaces']->getAll(), 'systemId', 'object.name');
            $this->dataInterface = $this->select("Choose interface", $interfaces);
        }

        return $this->_interface;
    }

    /**
     * Set interface.
     */
    public function setDataInterface($value)
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
        return array_merge(parent::options($id), ['dataInterface', 'verbose']);
    }
}
