<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;

class Section extends PanelWidget {
	public $gridClass = 'infinite\web\grid\Grid';
	public $section;

	public function init()
	{
		parent::init();
		if (isset($this->section)) {
			$this->icon = $this->section->icon;
			$this->title = $this->section->sectionTitle;
		}
	}

	public function generateStart()
	{
		$parts = [];
		$parts[] = Html::tag('div', '', ['id' => 'section-'.$this->systemId, 'class' => 'scroll-mark']);
		$parts[] = parent::generateStart();

		return implode('', $parts);
	}

	public function widgetCellSettings()
	{
		return [
			'mediumDesktopColumns' => 12,
			'tabletColumns' => 12,
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


	public function getWidgets() {
		$widgets = $this->collectorItem->getAll();
		ArrayHelper::multisort($widgets, ['object.displayPriority', 'object.name'], [SORT_ASC, SORT_ASC]);
		return $widgets;
	}
}
?>