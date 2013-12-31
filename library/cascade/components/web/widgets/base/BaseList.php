<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use cascade\components\web\widgets\Widget;

class BaseList extends Widget implements ObjectWidgetInterface, ListWidgetInterface {
	use ListWidgetTrait, ObjectWidgetTrait {
		ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
		ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
	}
}