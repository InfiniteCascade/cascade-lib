<?php
namespace cascade\components\base;

abstract class ModuleSetExtension extends \yii\base\Extension
{
	abstract public function getModules();
	
	public function init()
	{
		Yii::$app->modules = $this->modules;
	}
}

?>