<?php
namespace cascade\components\storageHandlers;

use Yii;

use yii\base\Event;

abstract class Module extends \cascade\components\base\CollectorModule {
	public $name;
	public $version = 1;

	public function getCollectorName() {
		return 'storageHandlers';
	}

	public function getModuleType() {
		return 'Storage';
	}
}
?>