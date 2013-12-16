<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;

class SingleSection extends Section {
	public $section;

	public function getCell()
	{
		$singleWidget = $this->singleWidget;
		if ($singleWidget) {
			$widgetCell = Yii::$app->collectors['widgets']->build($singleWidget->object);
			$widgetCell->prepend(Html::tag('div', '', ['id' => 'section-'.$this->systemId, 'class' => 'scroll-mark']));
			return $widgetCell;
		}
		return false;
	}

	public function getSingleWidget() {
		$widgets = $this->collectorItem->getAll();
		if (!empty($widgets)) {
			return array_shift($widgets);
		}
		return false;
	}
}
?>