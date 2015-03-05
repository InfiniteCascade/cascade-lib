<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use infinite\helpers\Match;
use yii\db\ColumnSchema;

/**
 * MultilineDetector [[@doctodo class_description:cascade\components\db\fields\MultilineDetector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class MultilineDetector extends \infinite\base\Object
{
    /*
     */
    /*
     * @var [[@doctodo var_type:_machineTests]] $_machineTests [[@doctodo var_description:_machineTests]]
     */
    static $_machineTests = [
        '/text/',
        '/blob/',
    ];

    /*
     * @return unknown
     */
    /**
     * [[@doctodo method_description:test]].
     *
     * @param yii\db\ColumnSchema $column [[@doctodo param_description:column]]
     *
     * @return [[@doctodo return_type:test]] [[@doctodo return_description:test]]
     */
    public static function test(ColumnSchema $column)
    {
        foreach (static::$_machineTests as $test) {
            $t = new Match($test);
            if ($t->test($column->dbType)) {
                return true;
            }
        }

        return false;
    }

    /*
     * @param unknown $test
     * @return unknown
     */
    /**
     * [[@doctodo method_description:registerMachineTest]].
     *
     * @return [[@doctodo return_type:registerMachineTest]] [[@doctodo return_description:registerMachineTest]]
     */
    public static function registerMachineTest($test)
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
