<?php
namespace cascade\components\web\widgets\base;

use Yii;

use \infinite\helpers\Html;

class SideSection extends Section {
	public function init()
	{
		parent::init();
		$this->title = false;
		$this->icon = false;
	}

	public function widgetCellSettings()
	{
		return [
			'mediumDesktopColumns' => 12,
			'tabletColumns' => 6,
			'baseSize' => 'tablet'
		];
	}

	public function generateContent()
	{
		$items = [];
		foreach ($this->widgets as $widget) {
			$items[] = $cell = Yii::$app->collectors['widgets']->build($widget->object);
			Yii::configure($cell, $this->widgetCellSettings());
		}
		$grid = Yii::createObject(['class' => $this->gridClass, 'cells' => $items]);
		return $grid->generate();
	}

	public function isSingle()
	{
		return false;
	}
}
?>