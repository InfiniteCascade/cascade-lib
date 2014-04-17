<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use infinite\helpers\Match;
use yii\db\ColumnSchema;

/**
 * MultilineDetector [@doctodo write class description for MultilineDetector]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class MultilineDetector extends \infinite\base\Object
{
    /**
     * @var __var__machineTests_type__ __var__machineTests_description__
     */
    static $_machineTests = [
        '/text/',
        '/blob/',
    ];

    /**
     * __method_test_description__
     * @param yii\db\ColumnSchema $column __param_column_description__
     * @return unknown
     */
    static function test(ColumnSchema $column)
    {
        foreach (static::$_machineTests as $test) {
            $t = new Match($test);
            if ($t->test($column->dbType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * __method_registerMachineTest_description__
     * @param unknown $test
     * @return unknown
     */
    static function registerMachineTest($test)
    {
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
