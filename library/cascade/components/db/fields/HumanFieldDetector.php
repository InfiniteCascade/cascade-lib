<?php
/**
 * ./app/components/objects/fields/HumanFieldDetector.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields;

use infinite\helpers\Match;

class HumanFieldDetector extends \infinite\base\Object {
	static $_machineTests = array(
		'id',
		'/\_id$/',
		'_moduleHandler',
		'created',
		'modified',
		'deleted',
	);

	/**
	 *
	 *
	 * @param unknown $name
	 * @return unknown
	 */
	static function test($name) {
		foreach (static::$_machineTests as $test) {
			$t = new Match($test);
			if ($t->test($name)) {
				return false;
			}
		}
		return true;
	}


	/**
	 *
	 *
	 * @param unknown $test
	 * @return unknown
	 */
	static function registerMachineTest($test) {
		if (is_array($test)) {
			foreach ($test as $t) {
				self::registerMachineTest($t);
			}
			return true;
		}
		self::$_machineTests[] = $test;
		return true;
	}


}


?>
