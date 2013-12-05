<?php
namespace cascade\components\dataInterface;

use \cascade\models\DataInterfaceLog;

class Action extends \infinite\base\Object {
	protected $_interface;
	protected $_status;
	protected $_log;
	protected $_id;
	protected $_settings = array();
	protected $_registry = array();

	public function __construct(Item $interface = null, $resumeLog = null) {
		$this->_interface = $interface;
		if (!is_null($resumeLog)) {
			$this->_log = $resumeLog;
		}
	}

	public function setSettings($value) {
		$this->_settings = $value;
	}

	public function getSettings() {
		return $this->_settings;
	}

	public function start() {
		return $this->save() && $this->_id = $this->log->id;
	}

	public function end($endInterrupted = false) {
		if (!is_null($this->log->ended)) { return true; }
		if ($endInterrupted) {
			$lerror = error_get_last();
			if (!empty($lerror)) {
				$this->status->addError("{$lerror['file']}:{$lerror['line']} {$lerror['message']}");
			}
			$this->log->status = 'interrupted';
		} elseif ($this->status->error) {
			$this->log->status = 'failed';
		} else {
			$this->log->status = 'success';
		}
		$this->log->ended = date("Y-m-d G:i:s");
		return $this->save();
	}

	public function save() {
		$this->log->message = serialize($this->status);
		$newPeak = memory_get_usage();
		if ($newPeak > $this->log->peak_memory) {
			$this->log->peak_memory = $newPeak;
		}
		if (empty($this->_interface)) {
			return true;
		}
		return $this->log->save();
	}

	public function getLog() {
		if (is_null($this->_log)) {
			$this->_log = new DataInterfaceLog;
			if (!empty($this->_interface)) {
				$this->_log->data_interface_id = $this->_interface->interfaceObject->id;
			}
			$this->_log->status = 'running';
			$this->_log->started = date("Y-m-d G:i:s");
			$this->_log->peak_memory = memory_get_usage();
		}
		return $this->_log;
	}

	public function getStatus() {
		if (!isset($this->_status)) {
			$this->_status = new Status($this);
		}
		return $this->_status;
	}

	public function getId() {
		return $this->_id;
	}

	public function addRegistry($key, $objectId) {
		$this->_registry[$key] = $objectId;
	}

	public function getRegistry() {
		return $this->_registry;
	}

	public function objectInRegistry($objectId) {
		return in_array($objectId, $this->_registry);
	}

	public function keyInRegistry($keyId) {
		return isset($this->_registry[$keyId]);
	}
}
?>