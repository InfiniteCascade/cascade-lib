<?php
/**
 * ./app/components/objects/fields/HumanFieldDetector.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields;

use infinite\helpers\Match;
use yii\db\ColumnSchema;

class MultilineDetector extends \infinite\base\Object {
	static $_machineTests = [
		'/text/',
		'/blob/',
	];

	/**
	 *
	 *
	 * @param unknown $name
	 * @return unknown
	 */
	static function test(ColumnSchema $column) {
		foreach (static::$_machineTests as $test) {
			$t = new Match($test);
			if ($t->test($column->dbType)) {
				return true;
			}
		}
		return false;
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
