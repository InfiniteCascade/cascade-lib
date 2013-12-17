<?php
namespace cascade\modules\TypePostalAddress\widgets;

use Yii;

use infinite\helpers\Html;
use infinite\helpers\StringHelper;

class EmbeddedList extends \cascade\components\web\widgets\base\SideList
{
	public $renderContentTemplate = ['name' => ['class' => 'list-group-item-heading', 'tag' => 'h5'], 'address1', 'address2', 'csz', 'uniqueCountry'];
	
	public function getMenuItems($model, $key, $index)
	{
		$base = parent::getMenuItems($model, $key, $index);
		$base['map'] = [
			'icon' => 'fa fa-globe',
			'label' => 'View map',
			'url' => StringHelper::parseText(Yii::$app->params['helperUrls']['map'], ['object' => $model]),
			'linkOptions' => ['target' => '_blank']
		];
		return $base;
	}
}
