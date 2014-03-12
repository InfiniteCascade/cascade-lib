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
			$content[] = Html::tag('span', '', ['class' => 'fa fa-reply']) .'&nbsp; '. Html::a('Go back to <em>' . Yii::$app->request->previousObject->descriptor .'</em>', Yii::$app->request->previousObject->getUrl('view', [], false));
			$content[] = Html::endTag('div');
		}
		$content[] = Html::beginTag('div', ['class' => 'ic-object-header']);
		$content[] = $object->descriptor;
		$content[] = Html::endTag('div');

		$menu = [];
		$familiarty = $object->getFamiliarity();
		$startWatchingItem = [
			'label' => Html::tag('span', '', ['class' => 'fa fa-eye']) .' Watch', 
			'url' => $object->getUrl('watch'), 
			'options' => [
				'title' => 'Watch and get notified with changes',
				'data-handler' => 'background',
				'class' => 'watch-link',
				'data-watch-task' => 'start',
			]
		];
		$stopWatchingItem = [
			'label' => Html::tag('span', '', ['class' => 'fa fa-check']) .' Watching',
			'url' => $object->getUrl('watch', ['stop' => 1]),
			'options' => [
				'title' => 'Stop receiving change notifications',
				'data-handler' => 'background',
				'data-watch-task' => 'stop',
				'class' => 'watch-link',
			]
		];
		$startWatchingItem['companion'] = $stopWatchingItem;
		$stopWatchingItem['companion'] = $startWatchingItem;
		if (empty($familiarty->watching)) {
			$menu[] = $startWatchingItem;
		} else {
			$menu[] = $stopWatchingItem;
		}
		if ($object->can('update')) {
			$menu[] = [
				'label' => Html::tag('span', '', ['class' => 'fa fa-shield']) .' Access', 
				'url' => $object->getUrl('privacy'), 
				'options' => ['title' => 'Manage access privileges', 'data-handler' => 'background']
			];
		}
		if ($object->can('update')) {
			$menu[] = [
				'label' => Html::tag('span', '', ['class' => 'fa fa-wrench']) .' Update', 
				'url' => $object->getUrl('update'), 
				'options' => ['title' => 'Update', 'data-handler' => 'background']
			];
		}
		if ($object->can('delete')) {
			$menu[] = [
				'label' => Html::tag('span', '', ['class' => 'fa fa-trash-o']) .' Delete', 
				'url' => $object->getUrl('delete'), 
				'options' => ['title' => 'Delete', 'data-handler' => 'background']
			];
		}
		if (!empty($menu)) {
			$content[] = Html::beginTag('div', ['class' => 'ic-object-menu columns-'. count($menu)]);
			$content[] = Html::beginTag('ul', ['class' => 'clearfix']);
			$menuCount = 0;
			foreach ($menu as $item) {
				$menuCount++;
				if (!isset($item['options'])) { $item['options'] = []; }
				$companionContent = '';
				if (isset($item['companion'])) {
					$companion = $item['companion'];
					if (!isset($companion['options'])) { $companion['options'] = []; }
					Html::addCssClass($companion['options'], 'hidden');
					$companionContent = Html::a($companion['label'], $companion['url'], $companion['options']);
				}
				$content[] = Html::tag('li', $companionContent . Html::a($item['label'], $item['url'], $item['options']), ['class' => 'item-'.$menuCount]);
			}
			$content[] = Html::endTag('ul');
			$content[] = Html::endTag('div');
		}
		$content[] = Html::endTag('div');
		return implode($content);
	}
}