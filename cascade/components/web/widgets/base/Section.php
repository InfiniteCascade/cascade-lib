<?php
namespace cascade\components\web\widgets\base;

use Yii;

use \infinite\helpers\Html;

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
		$parts[] = Html::tag('div', '', ['id' => 'section-'.$this->section->systemId, 'class' => 'scroll-mark']);
		
		if ($this->isSingle) {
			Html::addCssClass($this->htmlOptions, 'single-section');
			$parts[] = Html::beginTag('div', $this->htmlOptions);
		} else {
			$parts[] = parent::generateStart();
		}

		return implode('', $parts);
	}

	public function generateHeader()
	{
		if (!$this->isSingle) {
			return parent::generateHeader();
		}
		return null;
	}

	public function generateEnd()
	{
		if ($this->isSingle) {
			$parts = [];
			$parts[] = Html::endTag('div');
			return implode("", $parts);
		}
		return parent::generateEnd();
	}

	public function generateContent()
	{
		$items = [];
		foreach ($this->widgets as $widget) {
			$items[] = $cell = Yii::$app->collectors['widgets']->build($widget->object);
			if ($this->isSingle) {
				$cell->columns = 12;
			}
		}
		$grid = Yii::createObject(['class' => $this->gridClass, 'cells' => $items]);
		return $grid->generate();
	}

	public function getIsSingle() {
		return count($this->widgets) === 1;
	}

	public function getWidgets() {
		return $this->section->getAll();;
	}
}
?>