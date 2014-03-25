<?php
namespace cascade\components\web\widgets\base;

use Yii;
use yii\helpers\Url;

use infinite\helpers\Html;

use yii\bootstrap\Button;

class SimpleLinkList extends BaseList
{
	public $renderPager = false;

	public function getGridCellSettings()
	{
		return [
			'columns' => 3,
			'maxColumns' => 3,
			'tabletSize' => false
		];
	}

	public function getListOptions()
	{
		return array_merge(parent::getListOptions(), ['tag' => 'div']);
	}
	
	public function getListItemOptions($model, $key, $index)
	{
		return array_merge(parent::getListItemOptions($model, $key, $index), ['tag' => 'a', 'href' => Url::to($model->getUrl('view'))]);
	}

	public function getMenuItems($model, $key, $index)
	{
		return [];
	}

	public function contentTemplate($model)
	{
		return [
			'descriptor' => ['class' => 'list-group-item-heading', 'tag' => 'h5']
		];
	}

	public function renderItemContent($model, $key, $index){
		return parent::renderItemContent($model, $key, $index);
	}
}