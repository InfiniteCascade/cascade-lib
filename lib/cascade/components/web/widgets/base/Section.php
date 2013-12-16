<?php
namespace cascade\components\web\widgets\base;

use Yii;

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


	public function generateContent()
	{
		$items = [];
		foreach ($this->widgets as $widget) {
			$items[] = $cell = Yii::$app->collectors['widgets']->build($widget->object);
		}
		$grid = Yii::createObject(['class' => $this->gridClass, 'cells' => $items]);
		return $grid->generate();
	}


	public function getWidgets() {
		return $this->collectorItem->getAll();;
	}
}
?>