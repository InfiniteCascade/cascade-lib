<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;

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
	public function getMenuItems($model, $key, $index)
	{
		return [];
	}
	public function renderItemContent($model, $key, $index){
		if (!isset($this->renderContentTemplate)) {
			$this->renderContentTemplate = [
				'descriptor' => ['class' => 'list-group-item-heading', 'tag' => 'h5']
			];
		}
		return parent::renderItemContent($model, $key, $index);
	}
}