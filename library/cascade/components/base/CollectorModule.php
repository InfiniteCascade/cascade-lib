<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\base;

use infinite\base\collector\CollectedObjectTrait;
use infinite\base\exceptions\Exception;
use Yii;

/**
 * CollectorModule [[@doctodo class_description:cascade\components\base\CollectorModule]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class CollectorModule extends \infinite\base\Module implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * Prepares object for serialization.
     *
     * @throws \ [[@doctodo exception_description:\]]
     * @return [[@doctodo return_type:__sleep]] [[@doctodo return_description:__sleep]]
     *
     */
    public function __sleep()
    {
        $keys = array_keys((array) $this);
        if ($this->module !== Yii::$app) {
            throw new \Exception(get_class($this->module));
        }
        $this->module = null;

        return $keys;
    }

    /**
     * [[@doctodo method_description:__wakeup]].
     */
    public function __wakeup()
    {
        $this->module = Yii::$app;
        $this->always();
    }

    /**
     * [[@doctodo method_description:always]].
     *
     * @return [[@doctodo return_type:always]] [[@doctodo return_description:always]]
     */
    public function always()
    {
        return true;
    }
    /**
     * Get collector name.
     */
    abstract public function getCollectorName();

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent, $config = null)
    {
        if (isset(Yii::$app->params['modules'][$id])) {
            if (is_array($config)) {
                $config = array_merge_recursive($config, Yii::$app->params['modules'][$id]);
            } else {
                $config = Yii::$app->params['modules'][$id];
            }
        }
        if ($this->collectorName) {
            if (!isset(Yii::$app->collectors[$this->collectorName])) {
                throw new Exception('Cannot find the collector ' . $this->collectorName . '!');
            }
            if (!(Yii::$app->collectors[$this->collectorName]->register(null, $this))) {
                throw new Exception('Could not register ' . $this->shortName . ' in ' . $this->collectorName . '!');
            }
        }
        $this->loadSubmodules();

        Yii::$app->collectors->onAfterInit([$this, 'onAfterInit']);

        parent::__construct($id, $parent, $config);
    }

    /**
     * [[@doctodo method_description:loadSubmodules]].
     *
     * @return [[@doctodo return_type:loadSubmodules]] [[@doctodo return_description:loadSubmodules]]
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
     * Get submodules.
     *
     * @return [[@doctodo return_type:getSubmodules]] [[@doctodo return_description:getSubmodules]]
     */
    public function getSubmodules()
    {
        return [];
    }

    /**
     * [[@doctodo method_description:onAfterInit]].
     *
     * @return [[@doctodo return_type:onAfterInit]] [[@doctodo return_description:onAfterInit]]
     */
    public function onAfterInit($event)
    {
        return true;
    }
}
