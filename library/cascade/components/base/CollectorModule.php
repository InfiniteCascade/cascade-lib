<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\base;

use Yii;
use infinite\base\exceptions\Exception;
use infinite\base\collector\CollectedObjectTrait;

/**
 * CollectorModule [@doctodo write class description for CollectorModule]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class CollectorModule extends \infinite\base\Module implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * Get collector name
     */
    abstract public function getCollectorName();

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent, $config=null)
    {
        if (isset(Yii::$app->params['modules'][$id])) {
            if (is_array($config)) {
                $config = array_merge_recursive($config, Yii::$app->params['modules'][$id]);
            } else {
                $config = Yii::$app->params['modules'][$id];
            }
        }
        if ($this->collectorName) {
        	if (!isset(Yii::$app->collectors[$this->collectorName])) { throw new Exception('Cannot find the collector '. $this->collectorName .'!'); }
        	if (!(Yii::$app->collectors[$this->collectorName]->register(null, $this))) { throw new Exception('Could not register '. $this->shortName .' in '. $this->collectorName .'!'); }
    	}
        $this->loadSubmodules();

        Yii::$app->collectors->onAfterInit([$this, 'onAfterInit']);

        parent::__construct($id, $parent, $config);
    }

    /**
     * __method_loadSubmodules_description__
     * @return __return_loadSubmodules_type__ __return_loadSubmodules_description__
     */
    public function loadSubmodules()
    {
        $this->modules = $this->submodules;

        foreach ($this->submodules as $module => $settings) {
            $mod = $this->getModule($module);
            $mod->init();
        }

        return true;
    }

    /**
     * Get submodules
     * @return __return_getSubmodules_type__ __return_getSubmodules_description__
     */
    public function getSubmodules()
    {
        return [];
    }

    /**
     * __method_onAfterInit_description__
     * @param __param_event_type__        $event __param_event_description__
     * @return __return_onAfterInit_type__ __return_onAfterInit_description__
     */
    public function onAfterInit($event)
    {
        return true;
    }
}
