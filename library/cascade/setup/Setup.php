<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\setup;

/**
 * Setup [[@doctodo class_description:cascade\setup\Setup]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Setup extends \teal\setup\Setup
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
