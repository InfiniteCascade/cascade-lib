<?php
namespace cascade\components\web\widgets\base;

use Yii;

use \infinite\helpers\Html;

use yii\bootstrap\Button;

class SimpleLinkList extends BaseList {


	public function getListOptions()
	{
		return array_merge(parent::getListOptions(), ['tag' => 'div']);
	}
	public function getListItemOptions($model, $key, $index)
	{
		return array_merge(parent::getListItemOptions($model, $key, $index), ['tag' => 'a', 'href' => Html::url($model->getUrl('view'))]);
	}

}