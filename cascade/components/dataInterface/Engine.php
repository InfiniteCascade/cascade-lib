<?php
/**
 * ./protected/components/page/RPageTypeRegistry.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\dataInterface;

use \cascade\models\DataInterface;

use \infinite\helpers\ArrayHelper;
use \infinite\base\exceptions\Exception;

use \yii\base\Event;
use \yii\base\Application;

class Engine extends \infinite\base\Engine {
	const EVENT_AFTER_INTERFACE_REGISTRY = 'afterInterfaceRegistry';
	protected $_interfaces = [];
	protected $_tableRegistry;


	public function getTableRegistry($shortName = null) {
		if (is_null($this->_tableRegistry)) {
			$om = DataInterface::find()->all();
			$this->_tableRegistry = ArrayHelper::map($om, 'system_id');
		}
		if (!is_null($shortName)) {
			if (isset($this->_tableRegistry[$shortName])) {
				return $this->_tableRegistry[$shortName];
			}
			return false;
		}
		return $this->_tableRegistry;
	}

	/**
	 *
	 *
	 * @param object  $mod
	 * @return unknown
	 */
	public function add(Module $mod) {
		$shortName = $mod->shortName;
		if (!isset($this->_interfaces[$shortName])) {
			$this->_interfaces[$shortName] = new Item($shortName);
			$this->_interfaces[$shortName]->module = $mod;
			Yii::trace("'{$shortName}' interface has been registered and loaded");
		} else {
			$this->_interfaces[$shortName]->module = $mod;
			Yii::trace("'{$shortName}' interface has been loaded");
		}

		return $this->_interfaces[$shortName];
	}


	/**
	 *
	 *
	 * @param unknown $mod
	 * @return unknown
	 */
	public function ref($mod = null) {
		if (is_null($mod)) {
			return $this->_interfaces;
		}
		if (!isset($this->_interfaces[$mod])) {
			$this->_interfaces[$mod] = new Item($mod);
			Yii::trace("'{$mod}' interface has been registered");
		}
		return $this->_interfaces[$mod];
	}


	/**
	 *
	 *
	 * @param unknown $remove
	 * @return unknown
	 */
	public function remove($remove) {
		if (is_object($remove) and $remove instanceof Module) {
			$shortName = $remove->shortName;
			unset($this->_interfaces[$shortName]);
			return true;
		} elseif (is_string($remove)) {
			unset($this->_interfaces[$remove]);
			return true;
		}

		return false;
	}


	/**
	 *
	 *
	 * @param unknown $test
	 * @param unknown $activeOnly (optional)
	 * @return unknown
	 */
	public function has($test, $activeOnly = true) {
		if (is_object($test) and $test instanceof Module) {
			$shortName = $test->shortName;
			return isset($this->_interfaces[$shortName]) && (!$activeOnly or $this->_interfaces[$shortName]->active);
		} elseif (is_string($test)) {
			return isset($this->_interfaces[$test]) && (!$activeOnly or $this->_interfaces[$test]->active);
		}
		return false;
	}


	/**
	 *
	 *
	 * @param unknown $get        (optional)
	 * @param unknown $activeOnly (optional)
	 * @return unknown
	 */
	public function get($get = null, $activeOnly = true) {
		if ($get === null) {
			$interfaces = array();
			foreach ($this->_interfaces as $k => $m) {
				if ($activeOnly AND !$m->active) { continue; }
				$interfaces[$k] = $m->module;
			}
			return $interfaces;
		}

		if (isset($this->_interfaces[$get]) and !($activeOnly and !$this->_interfaces[$get]->active)) {
			return $this->_interfaces[$get]->module;
		}
		return false;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function beforeRequest() {
		Yii::beginProfile('ModuleLoad');
		foreach (Yii::$app->modules as $module => $settings) {
			if (preg_match('/^Interface/', $module) === 0) { continue; }
			$mod = Yii::$app->getModule($module);
		}
		if ( !(defined('INFINITE_APP_SETUP') AND INFINITE_APP_SETUP) ) {
			foreach ($this->_interfaces as $interface) {
				$mod = $interface->module;
				$shortName = $mod->shortName;
				// Database module registry
				$interfaceObject = $this->registerInterface($mod);
				$interface->interfaceObject = $interfaceObject;
				if (!isset($this->tableRegistry[$shortName])) {
					throw new Exception("Unable to initialize interface $shortName");
				}
			}
		}
		$this->trigger(self::EVENT_AFTER_INTERFACE_REGISTRY);
		Yii::endProfile('ModuleLoad');
		return parent::beforeRequest();
	}

	public function registerInterface($mod) {
		$shortName = $mod->shortName;
		if ( (defined('INFINITE_APP_SETUP') AND INFINITE_APP_SETUP) ) {
			return true;
		}

		if (!isset($this->tableRegistry[$shortName])) {
			$this->_tableRegistry[$shortName] = new DataInterface;
			$this->_tableRegistry[$shortName]->name = $mod->title;
			$this->_tableRegistry[$shortName]->system_id = $shortName;
			if (!$mod->setup() OR !$this->_tableRegistry[$shortName]->save()) {
				throw new Exception("Unable to register interface {$mod->title} ". print_r($this->_tableRegistry[$shortName]->errors, true));
				unset($this->_tableRegistry[$shortName]);
			}
		}
		return $this->_tableRegistry[$shortName];
	}


}


?>
