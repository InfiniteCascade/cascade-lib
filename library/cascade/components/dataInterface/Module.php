<?php
namespace cascade\components\dataInterface;

use Yii;

use yii\base\Event;

abstract class Module extends \cascade\components\base\CollectorModule {
	public $name;
	public $version = 1;

	public function getCollectorName() {
		return 'dataInterfaces';
	}

	public function getModuleType() {
		return 'Interface';
	}

	abstract public function getSettings();
	abstract public function run(Action $action);
}
?>