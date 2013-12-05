<?php
namespace cascade\components\dataInterface;

class Status extends \infinite\base\status\Status {
	protected $_action;
	protected $_errors = array();

	public function __construct($action) {
		$this->_action = $action;
	}

	public function __sleep() {
		$keys = array_keys((array)$this);
		$bad = array("\0*\0_action", "\0*\0_registry");
		foreach($keys as $k => $key) {
			if (in_array($key, $bad)) {
				unset($keys[$k]);
			}
		}
		return $keys;
	}

	public function addError($message) {
		$this->_errors[] = $message;
	}

	public function getError() {
		return !empty($this->_errors);
	}

}
?>