<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\base;

use Yii;

/**
 * ModuleSetExtension [@doctodo write class description for ModuleSetExtension]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class ModuleSetExtension implements \yii\base\BootstrapInterface
{
    /**
     * __method_bootstrap_description__
     * @param __param_app_type__ $app __param_app_description__
     */
    public function bootstrap($app)
    {
        Yii::beginProfile(get_called_class());
        Yii::$app->modules = static::getModules();
        Yii::endProfile(get_called_class());
        Yii::trace("Registered ".count(static::getModules())." modules in ". get_called_class());
    }

    /**
     * __method_getModules_description__
     * @return __return_getModules_type__ __return_getModules_description__
     */
    public static function getModules()
    {
        return [];
    }
}
