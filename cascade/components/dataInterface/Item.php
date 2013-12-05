<?php
/**
 * ./app/components/objects/RObjectType.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\dataInterface;

use \infinite\base\exceptions\Exception;

class Item extends \infinite\base\CollectorItem {
	public $error;

	private $_name;
	private $_module;
	private $_checked;
	protected $_interfaceObject;
	protected $_currentInterfaceAction;

	/**
	 *
	 */
	public function init() {
		parent::init();
	}


	public function run() {
		register_shutdown_function(array($this, 'saveLog'));

		$this->_currentInterfaceAction = new Action($this);
		if (!$this->_currentInterfaceAction->start()) {
			$this->error = 'Could not start interface action!';
			return false;
		}
		try {
			$this->module->run($this->_currentInterfaceAction);
		} catch (Exception $e) {
			$this->_currentInterfaceAction->status->addError('Exception raised: '. $e->getMessage());
			$this->_currentInterfaceAction->end(true);
			$this->error = 'Exception raised while running action ('. $e->getMessage() .').';
			return false;
		}
		$this->_currentInterfaceAction->end();
		return !$this->_currentInterfaceAction->status->error;
	}

	public function saveLog() {
		if (isset($this->_currentInterfaceAction)) {
			$this->_currentInterfaceAction->end(true);
		}

		return true;
	}


	public function setInterfaceObject($value) {
		$this->_interfaceObject = $value;
	}

	public function getInterfaceObject() {
		return $this->_interfaceObject;
	}

	/**
	 *
	 *
	 * @param unknown $name
	 */
	public function __construct($name) {
		$this->_name = $name;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getActive() {
		if (!is_null($this->_module) && $this->checked) {
			return true;
		}
		return false;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getChecked() {
		if (is_null($this->_checked)) {
			$this->_checked = true;
		}
		return $this->_checked;
	}

	public function getModule() {
		return $this->_module;
	}

	public function setModule($mod) {
		$this->_module = $mod;
	}
}