<?php
namespace cascade\components\web\widgets\base;

use Yii;

interface ListWidgetInterface
{
	public function renderItem($model, $key, $index);
	public function getListItemOptions($model, $key, $index);
	public function renderItemContent($model, $key, $index);
	public function renderItemMenu($model, $key, $index);
	public function getDataProvider();
	public function getDataProviderSettings();
	public function generateContent();
	public function getPaginationSettings();
}