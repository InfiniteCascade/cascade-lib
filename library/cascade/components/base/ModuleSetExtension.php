<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\base;

use Yii;

/**
 * ModuleSetExtension base class for a module set.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class ModuleSetExtension implements \yii\base\BootstrapInterface
{
    /**
     * Bootstrap the module set on load.
     */
    public function bootstrap($app)
    {
        Yii::beginProfile(get_called_class());
        Yii::$app->modules = static::getModules();
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        Yii::endProfile(get_called_class());
        Yii::trace("Registered " . count(static::getModules()) . " modules in " . get_called_class());
    }

    /**
     * Actions to run before request starts.
     */
    public function beforeRequest($event)
    {
    }

    /**
     * Get modules.
     *
     * @return array of the modules in the set
     */
    public static function getModules()
    {
        return [];
    }
}
