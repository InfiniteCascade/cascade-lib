<?php

namespace cascade\modules\WidgetWatching;

use Yii;

class Module extends \cascade\components\web\widgets\Module
{
	protected $_title = 'Watching';
	public $icon = 'fa fa-eye';
	public $priority = -99999;
	
	public $widgetNamespace = 'cascade\modules\WidgetWatching\widgets';


}
