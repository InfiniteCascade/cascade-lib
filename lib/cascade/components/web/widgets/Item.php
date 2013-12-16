<?php
/**
 * ./app/components/web/widgets/RWidgetItem.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


namespace cascade\components\web\widgets;

use Yii;

use infinite\base\collector\CollectedObjectTrait;

class Item extends \infinite\base\collector\Item implements \infinite\base\collector\CollectedObjectInterface {
	use CollectedObjectTrait;

	public $name;
	public $widget;
	public $tab;
	public $displayPriority = 0;
	public $locations = array();
	protected $_section;
	public $settings = [];


	public function getObject() {
		if (is_null($this->widget)) {
			return null;
		}
		$object = Yii::createObject($this->widget);
		$object->settings = $this->settings;
		$object->collectorItem = $this;
		return $object;
	}

	public function getSection($parent = null, $settings = array()) {
		$settings = array_merge($this->settings, $settings);
		if (is_null($this->_section)) {
			$this->_section = $this->owner->getSection($parent, $settings);
		}
		if (is_callable($this->_section) || (is_array($this->_section) && !empty($this->_section[0]) && is_object($this->_section[0]))) {
			return $this->evaluateExpression($this->_section, array('parent' => $parent, 'settings' => $settings));
		}
		return $this->_section;
	}

	public function setSection($value) {
		$this->_section = $value;
	}

}


?>
