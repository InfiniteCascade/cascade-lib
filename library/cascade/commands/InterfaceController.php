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
 * InterfaceController Run data interface commands.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class InterfaceController extends \infinite\console\Controller
{
    /**
     * @var Item the currently running interface
     */
    protected $_interface;
    /**
     * @var bool output debug and info messages
     */
    public $verbose;
    /**
     * Run a data interface.
     */
    public function actionIndex()
    {
        $this->actionRunOne();
    }

    /**
     * Run one data interface.
     */
    public function actionRunOne()
    {
        $this->out("Run Interface " . $this->dataInterface->object->name, Console::UNDERLINE, Console::FG_GREEN);
        $this->hr();
        $this->dataInterface->run(null, new ConsoleAction());
    }

    /**
     * Get interface.
     *
     * @return Item The data interface
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
     *
     * @param string $value system ID of data interface
     *
     * @throws Exception on invalid interface selection
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
