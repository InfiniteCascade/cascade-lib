<?php
namespace cascade\components\dataInterface;

class Collector extends \infinite\base\collector\Module {
	public function getCollectorItemClass() {
		return '\cascade\components\dataInterface\Item';
	}
	
	public function getModulePrefix() {
		return 'Interface';
	}
}
?>