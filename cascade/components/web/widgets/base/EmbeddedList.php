<?php
namespace cascade\components\web\widgets\base;
use infinite\helpers\Html;

class EmbeddedList extends EmbeddedWidget implements ListWidgetInterface {
	use ListWidgetTrait;

	public function getPaginationSettings() {
		return false;
	}
}