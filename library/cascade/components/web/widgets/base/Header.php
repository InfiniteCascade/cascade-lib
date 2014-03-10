<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use cascade\components\web\widgets\Widget;

class Header extends Widget {
	public function generateContent()
	{
		if (!isset(Yii::$app->request->object)) { return false; }
		$object = Yii::$app->request->object;
		$content = [];
		$content[] = Html::beginTag('div', ['class' => 'well ic-masthead']);
		if (Yii::$app->request->previousObject) {
			$content[] = Html::beginTag('div', ['class' => 'ic-object-previous']);
			$content[] = Html::tag('span', '', ['class' => 'fa fa-reply']) .'&nbsp; '. Html::a('Go back to <em>' . Yii::$app->request->previousObject->descriptor .'</em>', Yii::$app->request->previousObject->getUrl('view', false));
			$content[] = Html::endTag('div');
		}
		$content[] = Html::beginTag('div', ['class' => 'ic-object-header']);
		$content[] = Yii::$app->request->object->descriptor;
		$content[] = Html::endTag('div');

		$content[] = Html::beginTag('div', ['class' => 'ic-object-menu']);
		$content[] = 'menu';
		$content[] = Html::endTag('div');
		$content[] = Html::endTag('div');
		return implode($content);
	}
}