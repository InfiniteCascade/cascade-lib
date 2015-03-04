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
 * ModuleSetExtension [@doctodo write class description for ModuleSetExtension].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class ModuleSetExtension implements \yii\base\BootstrapInterface
{
    /**
     *
     */
    public function bootstrap($app)
    {
        Yii::beginProfile(get_called_class());
        Yii::$app->modules = static::getModules();
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        Yii::endProfile(get_called_class());
        Yii::trace("Registered " . count(static::getModules()) . " modules in " . get_called_class());
    }

    public function beforeRequest($event)
    {
    }

    /**
     * Get modules.
     */
    public static function getModules()
    {
        return [];
    }
}
