<?php
namespace cascade\components\base;

use Yii;
use infinite\base\exceptions\Exception;
use infinite\base\collector\CollectedObjectTrait;

abstract class CollectorModule extends \infinite\base\Module implements \infinite\base\collector\CollectedObjectInterface {
	use CollectedObjectTrait;
	
	abstract public function getCollectorName();

	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent, $config=null) {
		if (isset(Yii::$app->params['modules'][$id])) {
			if (is_array($config)) {
				$config = array_merge_recursive($config, Yii::$app->params['modules'][$id]);
			} else {
				$config = Yii::$app->params['modules'][$id];
			}
		}
		if (!isset(Yii::$app->collectors[$this->collectorName])) { throw new Exception('Cannot find the collector '. $this->collectorName .'!'); }
		if (!(Yii::$app->collectors[$this->collectorName]->register(null, $this))) { throw new Exception('Could not register '. $this->shortName .' in '. $this->collectorName .'!'); }
		$this->loadSubmodules();
		
		Yii::$app->collectors->onAfterInit([$this, 'onAfterInit']);

		
		parent::__construct($id, $parent, $config);
	}


	public function loadSubmodules() {
		$this->modules = $this->submodules;

		foreach ($this->submodules as $module => $settings) {
			$mod = $this->getModule($module);
			$mod->init();
		}
		return true;
	}

	public function getSubmodules() {
		return [];
	}


	public function onAfterInit($event) {
		return true;
	}
}

?>