<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\setup;

/**
 * Setup [[@doctodo class_description:cascade\setup\Setup]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Setup extends \canis\setup\Setup
{
    /**
     * @inheritdoc
     */
    public static function createSetupApplication($config = [])
    {
        if (is_null(self::$_instance)) {
            $className = __CLASS__;
            self::$_instance = new $className($config);
        }

        return parent::createSetupApplication($config);
    }
}
