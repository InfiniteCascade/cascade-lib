<?php
namespace cascade\components\web\form;

trait FormObjectTrait {
	public $owner;
	public $isValid = true;

	public function output() {
		echo $this->generate();
	}
	
	public function getGenerator() {
		if (is_null($this->owner)) { throw new \Exception("no owner! ". get_class($this)); return false; }
		if ($this->owner instanceof Generator) {
			return $this->owner;
		}
		return $this->owner->generator;
	}

	public function getSegment() {
		if (is_null($this->owner)) { return false; }
		if ($this->owner instanceof Segment) {
			return $this->owner;
		}
		return $this->owner->segment;
	}
}
?>