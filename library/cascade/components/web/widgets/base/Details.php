<?php
namespace cascade\components\web\widgets\base;
use infinite\helpers\Html;

class Details extends EmbeddedWidget implements ListWidgetInterface {
	use ListWidgetTrait, ObjectWidgetTrait {
		ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
		ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
	}

	public $details = [];

	public function generateContent()
	{
		return 'boom';
	}

	public function getPaginationSettings() {
		return false;
	}
}