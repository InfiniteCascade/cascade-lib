<?php
namespace cascade\components\section;

use Yii;

class Collector extends \infinite\base\collector\Module {
	public function getCollectorItemClass()
	{
		return '\cascade\components\section\Item';
	}

	public function getModulePrefix()
	{
		return 'Section';
	}

	public function getInitialItems()
	{
		return [
			'_side' => Yii::createObject(['class' => 'cascade\components\section\base\Side'])
		];
	}
}
?>