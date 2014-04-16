<?php
namespace cascade;

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
