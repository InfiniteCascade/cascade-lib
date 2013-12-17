<?php
class Cascade extends \yii\base\Extension
{
    /**
     * @inheritdoc
     */
    public static function init()
    {
    	parent::init();
        \Yii::setAlias('@cascade', __DIR__);
        \Yii::$app->registerMigrationAlias('@cascade/migrations');
    }
}