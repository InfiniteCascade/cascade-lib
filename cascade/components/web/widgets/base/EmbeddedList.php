<?php
namespace cascade\components\web\widgets\base;
use infinite\helpers\Html;

class EmbeddedList extends EmbeddedWidget implements ListWidgetInterface {
	use ListWidgetTrait, ObjectWidgetTrait {
		ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
		ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
	}

	public function getPaginationSettings() {
		return false;
	}
}