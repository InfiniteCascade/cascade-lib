<?php
namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;
use yii\bootstrap\Nav;

class BlankDecorator extends Decorator {
	public $gridCellClass = 'infinite\web\grid\Cell';
	
	public function generateHeader() {
		return null;
	}


	public function generateFooter() {
		return null;
	}
}
?>