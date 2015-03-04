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
 * CollectorModule [@doctodo write class description for CollectorModule].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class CollectorModule extends \infinite\base\Module implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    public function __sleep()
    {
        $keys = array_keys((array) $this);
        if ($this->module !== Yii::$app) {
            throw new \Exception(get_class($this->module));
        }
        $this->module = null;

        return $keys;
    }

    public function __wakeup()
    {
        $this->module = Yii::$app;
        $this->always();
    }

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
     *
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
     */
    public function getSubmodules()
    {
        return [];
    }

    /**
     *
     */
    public function onAfterInit($event)
    {
        return true;
    }
}
