<?php
namespace cascade\components\base;

use Yii;

abstract class ModuleSetExtension extends \yii\base\Extension
{
	static $_instance;
	abstract public function getModules();
	
	public static function getInstance()
	{
		if (!isset(self::$_instance)) {
			$className = get_called_class();
			self::$_instance = new $className;
		}
		return self::$_instance;
	}

	public static function init()
	{
		Yii::$app->modules = self::getInstance()->getModules();
	}
}

?>