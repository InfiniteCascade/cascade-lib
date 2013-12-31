<?php
/**
 * ./app/components/web/widgets/RBaseWidget.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web\widgets\base;

use Yii;

use cascade\components\helpers\StringHelper;

use infinite\helpers\Html;

use yii\bootstrap\Nav;

use infinite\base\collector\CollectedObjectTrait;
use infinite\base\ObjectTrait;
use infinite\base\ComponentTrait;
use infinite\web\grid\CellContentTrait;
use infinite\web\RenderTrait;

abstract class Widget extends \yii\bootstrap\Widget implements \infinite\base\WidgetInterface, \infinite\base\collector\CollectedObjectInterface {
	use CollectedObjectTrait;
	use ObjectTrait;
	use ComponentTrait;
	use CellContentTrait;
	use RenderTrait;

	public $owner;
	public $instanceSettings;

	public $title = false;
	public $icon = false;

	public $params = [];
	public $recreateParams = [];
	public $htmlOptions = ['class' => 'ic-widget '];

	protected $_widgetId;
	protected $_systemId;
	protected $_settings;

	abstract public function generateContent();

	// public function init() {
	// 	parent::init();
	// 	$backtrace = debug_backtrace();
	// 	$backtrace = $backtrace[4];
	// 	echo self::className() ." ({$backtrace['file']}:{$backtrace['line']})<br/>\n";
	// }

	public function behaviors()
	{
		return [
			'CellBehavior' => [
				'class' => 'cascade\\components\\web\\widgets\\base\\CellBehavior'
			]
		];
	}


	public function getHeaderMenu() {
		return [];
	}

	public function output() {
		echo $this->generate();
	}


	public function run() {
		echo $this->generate();
	}

	public function stateKeyName($key) {
		return 'widget.'.$this->systemId . '.'. $key;
	}

	public function getState($key, $default = null) {
		return Yii::$app->state->get($this->stateKeyName($key), $default);
	}

	public function setState($key, $value) {
		return Yii::$app->state->set($this->stateKeyName($key), $value);
	}

	public function generateStart() {
		$parts = [];
		foreach ($this->widgetClasses as $class) {
			Html::addCssClass($this->htmlOptions, $class);
		}
		$parts[] = Html::beginTag('div', $this->htmlOptions);
		return implode("", $parts);
	}

	public function generateEnd() {
		$parts = [];
		$parts[] = Html::endTag('div'); // panel
		return implode("", $parts);
	}

	public function generateHeader() {
		return null;
	}

	public function generateFooter() {
		return null;
	}

	public function generate() {
		Yii::beginProfile(get_called_class() .':'. __FUNCTION__);
		$content = $this->generateContent();
		if ($content === false) { return; }
		$result = $this->generateStart() . $this->generateHeader() . $content . $this->generateFooter() . $this->generateEnd();
		Yii::endProfile(get_called_class() .':'. __FUNCTION__);
		return $result;
	}

	public function parseText($text) {
		return StringHelper::parseText($text, $this->variables);
	}

	public function getVariables() {
		return [];
	}

	public function getWidgetClasses() {
		return [];
	}
	/**
	 *
	 *
	 * @return unknown
	 */
	public function getSettings() {
		return $this->_settings;
	}


	/**
	 *
	 *
	 * @param unknown $state
	 */
	public function setSettings($settings) {
		$this->_settings = $settings;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getWidgetId() {
		if (!is_null($this->_widgetId)) {
			return $this->_widgetId;
		}
		return $this->_widgetId = 'ic-widget-'.md5(uniqid());
	}

	public function setWidgetId($value) {
		$this->_widgetId = $value;
	}
	
	/**
	 *
	 *
	 * @return unknown
	 */
	public function getSystemId() {
		if (!isset($this->_systemId)) {
			if (isset($this->collectorItem) && isset($this->collectorItem->systemId)) {
				$this->_systemId = $this->collectorItem->systemId;
			}
		}
		return $this->_systemId;
	}

	public function setSystemId($value)
	{
		$this->_systemId = $value;
	}
}


?>
