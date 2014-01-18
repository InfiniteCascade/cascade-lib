<?php
namespace cascade\commands;

use Yii;

use yii\db\Query;
use yii\helpers\Console;
use yii\console\Exception;

use infinite\helpers\ArrayHelper;

class InterfaceController extends \infinite\console\Controller {
	protected $_interface;
	public $verbose;

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
		if (is_null($this->_interface)) {
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

	public function globalOptions()
	{
		return array_merge(parent::globalOptions(), ['interface', 'verbose']);
	}
}
?>