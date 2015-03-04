<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup;

/**
 * Setup [@doctodo write class description for Setup].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Setup extends \infinite\setup\Setup
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
