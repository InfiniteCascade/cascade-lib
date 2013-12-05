<?php
namespace cascade\setup\tasks;

use infinite\setup\Exception;

class Task_000006_collectors extends \infinite\setup\Task {
	public function getTitle() {
		return 'Collector Item Setup';
	}
	
	public function test() {
		return $this->setup->app()->collectors->areReady();
	}
	public function run() {
		return $this->setup->app()->collectors->initialize();
	}
	public function getFields() {
		return false;
	}
}
?>