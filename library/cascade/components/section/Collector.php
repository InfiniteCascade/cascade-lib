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
			'_side' => ['object' => Yii::createObject(['class' => 'cascade\components\web\widgets\base\SideSection']), 'priority' => false],
			'_parents' => ['object' => Yii::createObject(['class' => 'cascade\components\web\widgets\base\ParentSection']), 'priority' => 1000000, 'title' => 'Related']
		];
	}
}
?>