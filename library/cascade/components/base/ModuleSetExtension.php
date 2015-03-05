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
 * ModuleSetExtension [[@doctodo class_description:cascade\components\base\ModuleSetExtension]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class ModuleSetExtension implements \yii\base\BootstrapInterface
{
    /**
     * [[@doctodo method_description:bootstrap]].
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
     * [[@doctodo method_description:beforeRequest]].
     */
    public function beforeRequest($event)
    {
    }

    /**
     * Get modules.
     *
     * @return [[@doctodo return_type:getModules]] [[@doctodo return_description:getModules]]
     */
    public static function getModules()
    {
        return [];
    }
}
