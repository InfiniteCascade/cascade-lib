<?php
namespace cascade\components\dataInterface;

use \infinite\base\exceptions\Exception;
use \infinite\base\language\Noun;

use yii\base\Event;

abstract class Module extends \infinite\base\Module {
	public $interfaceTitle;
	protected $_interfaceItem;

	public function __construct($id, $parent, $config=null) {
		parent::__construct($id, $parent, $config);
		if (!isset(Yii::$app->interfaces)) { throw new Exception('Cannot find the interfaces engine!'); }
		if (!($this->_interfaceItem = Yii::$app->interfaces->add($this))) { throw new Exception('Could not register interface '. $this->shortName .'!'); }
		Yii::$app->interfaces->on(Engine::EVENT_AFTER_INTERFACE_REGISTRY, [$this, 'onBeginRequest']);
	}

	abstract public function run(Event $event = null);
	
	public function onBeginRequest(Event $event) {
	}

	public function init() {
		return true;
	}

	public function setup() {
		return true;
	}

	public function getTitle() {
		if (is_object($this->interfaceTitle)) { return $this->interfaceTitle; }
		return new Noun($this->interfaceTitle);
	}

	public function getInterfaceItem() {
		return $this->_interfaceItem;
	}
}
?>