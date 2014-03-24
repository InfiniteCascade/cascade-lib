<?php
namespace cascade;

class Cascade implements \yii\base\BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap(\yii\base\Application $app)
    {
        \Yii::setAlias('@cascade', __DIR__);
        \Yii::$app->registerMigrationAlias('@cascade/migrations');
    }
}