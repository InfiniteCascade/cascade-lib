<?php
namespace cascade;

class Cascade extends \yii\base\Extension
{
    /**
     * @inheritdoc
     */
    public static function bootstrap()
    {
    	parent::bootstrap();
        \Yii::setAlias('@cascade', __DIR__);
        \Yii::$app->registerMigrationAlias('@cascade/migrations');
    }
}