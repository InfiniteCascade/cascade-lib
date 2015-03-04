<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade;

/**
 * Cascade [@doctodo write class description for Cascade].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Cascade implements \yii\base\BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        \Yii::setAlias('@cascade', __DIR__);
        \Yii::$app->registerMigrationAlias('@cascade/migrations');
    }
}
