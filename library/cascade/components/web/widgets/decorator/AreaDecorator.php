<?php
namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;
use yii\bootstrap\Nav;

class AreaDecorator extends Decorator {

	public $panelCssClass = 'panel';
	public $panelStateCssClass = 'panel-default';
	public $gridCellClass = 'infinite\web\grid\Cell';

	public function generateStart() {
		Html::addCssClass($this->owner->htmlOptions, $this->owner->panelCssClass);
		Html::addCssClass($this->owner->htmlOptions, $this->owner->panelStateCssClass);
		return parent::generateStart();
	}
	
	public function generateHeader() {
		$parts = [];
		$parts[] = Html::beginTag('div', ['class' => 'panel-body']);
		return implode("", $parts);
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