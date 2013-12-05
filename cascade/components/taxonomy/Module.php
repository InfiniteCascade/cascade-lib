<?php
namespace cascade\components\taxonomy;

use Yii;

use \yii\base\Event;

abstract class Module extends \cascade\components\base\CollectorModule {
	public $name;
	public $icon = 'ic-icon-info';
	public $priority = 1000;
	public $version = 1;

	public function getCollectorName() {
		return 'types';
	}

	abstract public function getSettings();
}
?>