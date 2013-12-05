<?php
namespace cascade\components\web\widgets;

use Yii;

use infinite\base\exceptions\Exception;

use yii\base\Controller;

abstract class Module extends \infinite\base\Module {
	public $title;
	public $icon = 'ic-icon-info';
	public $priority = 1000; //lower is better

	public $widgetNamespace;


	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent, $config=null) {
		Yii::$app->collectors->onAfterInit(array($this, 'onAfterInit'));

		if (isset(Yii::$app->controller)) {
			throw new Exception("This is a happy exception!");
			Yii::$app->controller->on(Controller::EVENT_BEFORE_ACTION, array($this, 'onBeforeControllerAction'));
		}
		
		parent::__construct($id, $parent, $config);
	}

	public function getModuleType() {
		return 'Widget';
	}

	public function onAfterInit($event) {
		if (isset(Yii::$app->collectors['widgets']) and !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) { throw new Exception('Could not register widgets for '. $this->systemId .'!'); }
	}

	public function widgets() {
		$widgets = [];
		$className = $this->widgetNamespace .'\\'. 'Content';
		@class_exists($className);
		if (class_exists($className, false)) {
			$summaryWidget = array();
			$id = $this->systemId .'Content';
			$summaryWidget['widget'] = [
				'class' => $className,
				'icon' => $this->icon, 
				// 'title' => '%%type.'. $this->systemId .'.title.upperPlural%%'
			];
			$summaryWidget['locations'] = array('front');
			$summaryWidget['displayPriority'] = $this->priority;
			$widgets[$id] = $summaryWidget;
		}
		//var_dump($widgets);exit;
		return $widgets;
	}

	public function getShortName() {
		preg_match('/Widget([A-Za-z]+)\\\Module/', get_class($this), $matches);
		if (!isset($matches[1])) {
			throw new Exception(get_class($this). " is not set up correctly!");
		}
		return $matches[1];
	}

}
?>