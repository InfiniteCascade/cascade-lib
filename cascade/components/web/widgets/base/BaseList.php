<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;

class BaseList extends PanelWidget implements ObjectWidgetInterface, ListWidgetInterface {
	use ListWidgetTrait;
	use ObjectWidgetTrait;
}