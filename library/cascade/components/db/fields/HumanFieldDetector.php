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
 * HumanFieldDetector [@doctodo write class description for HumanFieldDetector].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class HumanFieldDetector extends \infinite\base\Object
{
    /*
     */
    static $_machineTests = [
        'id',
        '/\_id$/',
        '/\_hash$/',
        '_moduleHandler',
        'created',
        'modified',
        'deleted',
        'archived',
    ];

    /*
     * @return unknown
     */
    public static function test(ColumnSchema $column)
    {
        foreach (static::$_machineTests as $test) {
            $t = new Match($test);
            if ($t->test($column->name)) {
                return false;
            }
        }

        return true;
    }

    /*
     * @param unknown $test
     * @return unknown
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
