<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;

class Details extends EmbeddedWidget implements ListWidgetInterface {
	use ListWidgetTrait, ObjectWidgetTrait {
		ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
		ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
	}

	public $title = 'Details';
	public $contentHtmlOptions = [];

	public function getHeaderMenu()
	{
		$menu = [];
		return $menu;
	}

	public function generateContent()
	{
		if (empty(Yii::$app->request->object)) { return false; }
		if (!($detailFields = Yii::$app->request->object->getDetailFields()) || empty($detailFields)) { return false; }
		$parts = [];
		Html::addCssClass($this->contentHtmlOptions, 'form-group');
		$parts[] = Html::beginTag('div', $this->contentHtmlOptions);
		foreach ($detailFields as $key => $field) {

		}
		$parts[] = Html::endTag('div');
		return implode($parts);
	}

	public function getPaginationSettings() {
		return false;
	}
}