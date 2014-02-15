<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use cascade\components\web\widgets\BaseWidget;

abstract class WidgetArea extends BaseWidget {
	public $location = 'right';
	public $parentWidget;
	public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\AreaDecorator';

	public function generate() {
		Yii::beginProfile(get_called_class() .':'. __FUNCTION__);
		$this->ensureDecorator();
		$content = $this->generateContent();
		if ($content === false) { return; }
		$result = $this->generateStart() . $this->generateHeader() . $content . $this->generateFooter() . $this->generateEnd();
		Yii::endProfile(get_called_class() .':'. __FUNCTION__);
		return $result;
	}
	public function getCellContent()
	{
		return $this->generate();
	}
}