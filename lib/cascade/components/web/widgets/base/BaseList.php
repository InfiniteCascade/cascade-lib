<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;

class BaseList extends PanelWidget implements ObjectWidgetInterface, ListWidgetInterface {
	use ListWidgetTrait, ObjectWidgetTrait {
		ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
		ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
	}
}