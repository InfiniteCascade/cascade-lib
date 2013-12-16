<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use infinite\helpers\ArrayHelper;
use yii\bootstrap\ButtonDropdown;

trait ListWidgetTrait
{
	public $emptyMessage = 'No items exist.';
	public $renderContentTemplate;
	public $defaultContentRow = [
		'class' => 'list-group-item-text',
		'tag' => 'div'
	];


	public function renderItemContent($model, $key, $index){
		if (!isset($this->renderContentTemplate)) {
			if ($model->objectType->hasDashboard) {
				$this->renderContentTemplate = [
					'viewLink' => ['class' => 'list-group-item-heading', 'tag' => 'h5']
				];
			} else {
				$this->renderContentTemplate = [
					'descriptor' => ['class' => 'list-group-item-heading', 'tag' => 'h5']
				];
			}
		}
		$parts = [];
		foreach ($this->renderContentTemplate as $fieldName => $settings) {
			if (is_numeric($fieldName)) {
				$fieldName = $settings;
				$settings = [];
			}
			$settings = array_merge($this->defaultContentRow, $settings);
			$tag = $settings['tag'];
			unset($settings['tag']);
			if (!empty($model->{$fieldName})) {
				$parts[] = Html::tag($tag, $model->{$fieldName}, $settings);
			}
		}

		return implode("", $parts);
	}

	public function getListOptions()
	{
		return ['class' => 'list-group'];
	}

	public function getListItemOptions($model, $key, $index)
	{
		return ['class' => 'list-group-item'];
	}

	public function renderItem($model, $key, $index)
	{
		$listItemOptions = $this->getListItemOptions($model, $key, $index);
		$listTag = ArrayHelper::remove($listItemOptions, 'tag', 'li');

		$parts = [];
		$parts[] = $this->renderItemMenu($model, $key, $index);
		$parts[] = $this->renderItemContent($model, $key, $index);
		return Html::tag($listTag, implode('', $parts), $listItemOptions);
	}


	public function renderItemMenu($model, $key, $index)
	{
		$menuItems = $this->getMenuItems($model, $key, $index);
		if (!empty($menuItems)) {
			foreach ($menuItems as &$menuItem) {
				if (isset($menuItem['icon'])) {
					if (!isset($menuItem['label'])) { $menuItem['label'] = ''; }
					$menuItem['label'] = '<span class="'.$menuItem['icon'].'"></span>'. $menuItem['label'];
					unset($menuItem['icon']);
				}
			}
			/*
			<button class="btn dropdown-toggle sr-only" type="button" id="dropdownMenu1" data-toggle="dropdown">
    Dropdown
    <span class="caret"></span>
  </button>
  */

			return ButtonDropdown::widget([
					'label' => '',
					'options' => ['class' => 'pull-right'],
					'dropdown' => [
						'options' => ['class' => 'pull-right'],
						'encodeLabels' => false,
						'items' => $menuItems
					]
				]);
		}
		return null;
	}

	public function generateContent() {
		$results = $this->dataProvider;
		if (!empty($results->count)) {
			$models = $this->dataProvider->getModels();
			$keys = $this->dataProvider->getKeys();

			$listOptions = $this->listOptions;
			$listTag = ArrayHelper::remove($listOptions, 'tag', 'ul');

			$rows = [];
			$rows[] = Html::beginTag($listTag, $listOptions);
			foreach (array_values($models) as $index => $model) {
				$rows[] = $this->renderItem($model, $keys[$index], $index);
			}
			$rows[] = Html::endTag($listTag);
			return implode('', $rows);
		} else {
			return Html::tag('div', $this->emptyMessage, ['class' => 'empty-messages']);
		}
	}

	public function generateFooter()
	{
		$footer = '';
		$pager = $this->renderPager();
		if ($pager) {
			$footer = Html::tag('div', $pager, ['class' => 'panel-footer clearfix']);
		}
		return parent::generateFooter() . $footer;
	}

	/**
	 * Renders the pager.
	 * @return string the rendering result
	 */
	public function renderPager()
	{
		$pagination = $this->dataProvider->getPagination();
		if ($pagination === false || $pagination->getPageCount() <= 1) {
			return false;
		}
		/** @var LinkPager $class */
		$pager = $this->pagerSettings;
		$class = ArrayHelper::remove($pager, 'class', 'yii\widgets\LinkPager');
		$pager['pagination'] = $pagination;
		if (!isset($pager['options'])) {
			$pager['options'] = [];
		}
		Html::addCssClass($pager['options'], 'pagination pull-right');
		return $class::widget($pager);
	}


}