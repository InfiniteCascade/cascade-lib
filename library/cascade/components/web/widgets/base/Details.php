<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use cascade\components\web\widgets\Widget;

class Details extends Widget implements ListWidgetInterface {
	use ListWidgetTrait, ObjectWidgetTrait {
		ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
		ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
	}

	public $contentHtmlOptions = ['class' => 'form-panel'];
	public $fieldHtmlOptions = ['class' => 'form-group'];
	public $labelHtmlOptions = ['class' => 'control-label', ];
	public $valueHtmlOptions = ['class' => 'form-control-static'];

	public $gridClass = 'infinite\\web\\grid\\Grid';
	public $gridCellClass = 'infinite\\web\\grid\\Cell';
	protected $_title = 'Details';

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
		$parts[] = Html::beginTag('div', $this->contentHtmlOptions);

		$grid = Yii::createObject(['class' => $this->gridClass]);
		// $parts[] = '<pre>'. print_r(array_keys($detailFields), true) .'</pre>';
		foreach ($detailFields as $key => $field) {
			$fieldHtmlOptions = $this->fieldHtmlOptions;
			$labelCell = $this->generateCell(Html::tag('label', $field->label, $this->labelHtmlOptions));
			$valueCell = $this->generateCell(Html::tag('p', $field->formattedValue, $this->valueHtmlOptions));
			$row = $grid->addRow([$labelCell, $valueCell]);

			if ($field->multiline) {
				$labelCell->columns = 12;
				$valueCell->columns = 12;
				Html::addCssClass($row->htmlOptions, 'form-vertical');
			} else {
				$labelCell->columns = 5;
				$valueCell->columns = 7;
				Html::addCssClass($row->htmlOptions, 'form-horizontal');
			}
		}

		$parts[] = $grid->generate();
		$parts[] = Html::endTag('div');
		return implode($parts);
	}

	protected function generateCell($content)
	{
		return Yii::createObject(['class' => $this->gridCellClass, 'content' => $content, 'tabletSize' => false]);
	}

	public function getPaginationSettings() {
		return false;
	}
}