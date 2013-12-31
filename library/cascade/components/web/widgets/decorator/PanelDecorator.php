<?php
namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;
use yii\bootstrap\Nav;

class PanelDecorator extends Decorator {

	public $panelCssClass = 'panel';
	public $panelStateCssClass = 'panel-default';
	public $gridCellClass = 'infinite\web\grid\Cell';

	public function generatePanelTitle() {
		$parts = [];
		if ($this->owner->title) {
			$menu = $icon = null;
			$titleMenu = $this->owner->generateTitleMenu();
			if ($titleMenu) {
				$menu = $titleMenu;
			}
			if (!empty($this->owner->icon)) {
				$icon = Html::tag('i', '', ['class' => 'panel-icon '. $this->owner->icon]) . Html::tag('span', '', ['class' => 'break']);
			}
			$parts[] = Html::tag('div', Html::tag('h2', $icon . $this->owner->parseText($this->owner->title)) . $menu, ['class' => 'panel-heading']);
		}
		if (empty($parts)) {
			return false;
		}
		return implode("", $parts);
	}

	public function generateStart() {
		Html::addCssClass($this->owner->htmlOptions, $this->owner->panelCssClass);
		Html::addCssClass($this->owner->htmlOptions, $this->owner->panelStateCssClass);
		return parent::generateStart();
	}
	
	public function generateHeader() {
		$parts = [];
		$title = $this->owner->generatePanelTitle();
		if ($title) {
			$parts[] = $title;
		}
		$parts[] = Html::beginTag('div', ['class' => 'panel-body']);
		return implode("", $parts);
	}

	public function generateTitleMenu() {
		$menu = $this->owner->getHeaderMenu();
		if (empty($menu)) { return false; }
		$this->backgroundifyMenu($menu);
		return Nav::widget([
			'items' => $menu,
			'encodeLabels' => false,
			'options' => ['class' => 'pull-right nav-pills']
		]);
	}

	protected function backgroundifyMenu(&$items) {
		if (!is_array($items)) { return; }
		foreach ($items as $k => $v) {
			if (!isset($items[$k]['linkOptions'])) { $items[$k]['linkOptions'] = []; }
			if (!isset($items[$k]['linkOptions']['data-background'])) {
				$items[$k]['linkOptions']['data-handler'] = 'background';
			}
			if (isset($items[$k]['items'])) {
				$this->backgroundifyMenu($items[$k]['items']);
			}
		}
	}

	public function generateFooter() {
		$parts = [];
		$parts[] = Html::endTag('div'); // panel-body
		return implode("", $parts);
	}

	public function getPanelTitle()
	{
		return $this->owner->parseText($this->owner->title);
	}
}
?>