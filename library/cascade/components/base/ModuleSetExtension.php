<?php
namespace cascade\components\base;

use Yii;

abstract class ModuleSetExtension extends \yii\base\Extension
{
	public static function bootstrap()
	{
		Yii::beginProfile(get_called_class());
		Yii::$app->modules = static::getModules();
		Yii::endProfile(get_called_class());
		Yii::trace("Registered ".count(static::getModules())." modules in ". get_called_class());
	}

	public static function getModules()
	{
		return [];
	}
}

?>