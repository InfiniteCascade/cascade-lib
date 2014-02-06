<?php
/**
 * ./app/components/web/form/RFormGenerator.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web\form;

use Yii;

use infinite\helpers\Html;

class Generator extends \infinite\base\Object implements \infinite\web\RenderInterface
{
	protected $_items;
	public $form;

	public $models = [];

	public $isValid = true;
	public $class = '';
	public $ajax = false;

	public function setItems($items) {
		$this->_items = $items;
		if (isset($this->_items[0]) and is_array($this->_items[0])) {
			$this->_items = $this->_items[0];
		}
		foreach ($this->_items as $item) {
			$item->owner = $this;
			if (!$item->isValid) {
				$this->isValid = false;
			}
		}
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function generate() {
		if (empty($this->_items)) {
			return '';
		}
		$result = [];
		$formOptions = [
			'options' => ['class' => ''], //form-horizontal
			'enableClientValidation' => false
		];
		if (Yii::$app->request->isAjax) {
			Html::addCssClass($formOptions['options'], 'ajax');
		}

		if ($this->hasFile() && !isset($formOptions['options']['enctype'])) {
			$formOptions['options']['enctype'] = 'multipart/form-data';
		}

		list($this->form, $formStartRow) = ActiveForm::begin($formOptions, false);
		$result[] = $formStartRow;
		// $result[] = Html::beginForm('', 'post', array('class' => $this->class));
		$result[] = Html::beginTag('div', ['class' => '']);
		foreach ($this->_items as $item) {
			$result[] = $item->generate();
		}
		if (!Yii::$app->request->isAjax) {
			$result[] = Html::beginTag('div', ['class' => 'row form-group']);
			$result[] = Html::beginTag('div', ['class' => 'col-sm-12']);
			$result[] = Html::submitButton('Save', ['class' => 'btn btn-primary']);;
			$result[] = Html::endTag('div');
			$result[] = Html::endTag('div');
		}
		$result[] = Html::endTag('div');
		$result[] = ActiveForm::end(false);
		return implode("\n", $result);
	}

	public function hasFile()
	{
		foreach ($this->_items as $item) {
			if ($item->hasFile()) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 */
	public function output() {
		echo $this->generate();
	}
}


?>
