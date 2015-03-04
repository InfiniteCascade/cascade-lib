<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\components\db\ActiveRecord;
use Yii;
use yii\helpers\Inflector;

/**
 * DataSource [@doctodo write class description for DataSource].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataSource extends \infinite\base\Component
{
    const EVENT_LOAD_FOREIGN_DATA_ITEMS = 0x01;
    const EVENT_LOAD_LOCAL_DATA_ITEMS = 0x02;

    public $task;
    public $taskWeight = 1;

    /**
     */
    public $fieldMapClass = 'cascade\components\dataInterface\FieldMap';
    /**
     */
    public $dataItemClass = 'cascade\components\dataInterface\DataItem';
    /**
     */
    public $searchClass = 'cascade\components\dataInterface\Search';

    /**
     */
    public $keyGenerator;
    /**
     */
    public $debug = false;
    /**
     */
    public $lazyForeign = true;
    /**
     */
    public $lazyLocal = true;
    /**
     */
    public $childOnly = false;
    /**
     */
    public $postProcess = false;

    /**
     */
    public $ignoreForeign = false;
    /**
     */
    public $ignoreLocal = false;

    /**
     */
    public $baseAttributes = [];

    /**
     */
    protected $_localModel;
    /**
     */
    protected $_foreignModel;
    /**
     */
    protected $_map;
    /**
     */
    protected $_settings;
    /**
     */
    protected $_search;

    /**
     */
    protected $_foreignDataItems;
    /**
     */
    protected $_localDataItems;

    /**
     */
    public $name;
    /**
     */
    public $module;

    /**
     */
    protected $_countTotal;
    /**
     */
    protected $_countRemaining;

    /*
     */
    static $defaultSettings = [
        'direction' => 'to_local', // to_local, to_foreign, both
        'update' => true,
        'create' => true,
        'deleteLocal' => false,
        'deleteForeign' => false,
        'foreignPullParams' => [],
        'universalKey' => false,
    ];

    // abstract public function handleLocal($action, DataItem $dataItem, DataItem $parent = null);

    /**
     *
     */
    public function isReady()
    {
        return isset($this->localModel) && isset($this->foreignModel);
    }

    /**
     */
    public function clearCaches()
    {
        ActiveRecord::clearCache();
        \yii\caching\Dependency::resetReusableData();
        \cascade\components\types\Relationship::clearCache();
    }

    public function getDescriptor()
    {
        return Inflector::titleize($this->name, true);
    }

    /**
     * Get total.
     */
    public function getTotal()
    {
        if (!$this->isReady()) {
            return 0;
        }
        if (is_null($this->_countTotal)) {
            $this->_countTotal = 0;
            if (in_array($this->settings['direction'], ['to_local', 'both'])) {
                $this->_countTotal += count($this->foreignDataItems);
            }

            if (in_array($this->settings['direction'], ['to_foreign', 'both'])) {
                $this->_countTotal += count($this->localDataItems);
            }
        }

        return $this->_countTotal;
    }

    /**
     * Get done.
     */
    public function getDone()
    {
        return $this->total - $this->remaining;
    }

    /**
     * Get dummy local model.
     */
    public function getDummyLocalModel()
    {
        $localModelClass = $this->localModel;

        return new $localModelClass();
    }

    /**
     * Get remaining.
     */
    public function getRemaining()
    {
        if (is_null($this->_countRemaining)) {
            $this->_countRemaining = $this->total;
        }

        return $this->_countRemaining;
    }

    /**
     *
     */
    public function reduceRemaining(DataItem $dataItem)
    {
        $n = 0;
        // if foreign (handle does foreign -> local)
        if ($dataItem->isForeign && in_array($this->settings['direction'], ['to_local', 'both'])) {
            $n++;
        }

        // if local (handle does local -> foreign)
        if (!$dataItem->isForeign && in_array($this->settings['direction'], ['to_foreign', 'both'])) {
            $n++;
        }
        $this->task->reduceRemaining($n);
    }

    /**
     * Get foreign data items.
     */
    public function getForeignDataItems()
    {
        if (!isset($this->_foreignDataItems)) {
            $this->_foreignDataItems = [];
            $this->trigger(self::EVENT_LOAD_FOREIGN_DATA_ITEMS);
        }

        return $this->_foreignDataItems;
    }

    /**
     * Get local data items.
     */
    public function getLocalDataItems()
    {
        if (!isset($this->_localDataItems)) {
            $this->trigger(self::EVENT_LOAD_LOCAL_DATA_ITEMS);
        }

        return $this->_localDataItems;
    }

    /**
     * Get handled local data items.
     */
    public function getHandledLocalDataItems()
    {
        $handled = [];
        foreach ($this->localDataItems as $local) {
            if ($local->handled) {
                $handled[] = $local;
            }
        }

        return $handled;
    }

    public function universalFilter($value)
    {
        return $value;
    }

    public function prepareTask()
    {
        $total = 0;
        if (in_array($this->settings['direction'], ['to_local', 'both'])) {
            $total += count($this->foreignDataItems);
        }
        if (in_array($this->settings['direction'], ['to_foreign', 'both'])) {
            $total += count($this->localDataItems);
        }

        $this->task->setWeight($total*$this->taskWeight);
        $this->task->setProgressTotal($total);
    }

    /**
     *
     */
    public function run()
    {
        $task = $this->task;
        $task->start();
        if (!$this->isReady()) {
            $task->end();

            return false;
        }
        $action = $this->action;
        $this->settings = $action->config;

        if (in_array($this->settings['direction'], ['to_local', 'both'])) {
            // start foreign
            foreach ($this->foreignDataItems as $dataItem) {
                $dataItem->handler->handle($task);
                $this->clearCaches();
            }
        }

        if (in_array($this->settings['direction'], ['to_foreign', 'both'])) {
            // start local
            foreach ($this->localDataItems as $dataItem) {
                $dataItem->handler->handle($task);
                $this->clearCaches();
            }
        }
        $task->end();

        return true;
    }

    /**
     * Get data interface.
     */
    public function getDataInterface()
    {
        return $this->_dataInterface;
    }

    /**
     * Set settings.
     */
    public function setSettings($settings)
    {
        if (is_null($this->_settings)) {
            $this->_settings = self::$defaultSettings;
        }
        if (!is_array($settings)) {
            return true;
        }
        $this->_settings = array_merge($this->_settings, $settings);

        return true;
    }

    /**
     * Get settings.
     */
    public function getSettings()
    {
        if (is_null($this->_settings)) {
            $this->settings = [];
        }

        return $this->_settings;
    }

    /**
     * Get search.
     */
    public function getSearch()
    {
        return $this->_search;
    }

    /**
     * Set search.
     */
    public function setSearch($value)
    {
        if (!is_object($value)) {
            if (!isset($value['class'])) {
                $value['class'] = $this->searchClass;
            }
            $value = Yii::createObject($value);
        }
        $value->dataSource = $this;
        $this->_search = $value;
    }

    /**
     * Get local model.
     */
    public function getLocalModel()
    {
        return $this->_localModel;
    }

    /**
     * Set local model.
     */
    public function setLocalModel($value)
    {
        $this->_localModel = ActiveRecord::parseModelAlias($value);
    }

    /**
     * Set foreign model.
     */
    public function setForeignModel($value)
    {
        $this->_foreignModel = $value;
    }

    /**
     * Get foreign model.
     */
    public function getForeignModel()
    {
        return $this->_foreignModel;
    }

    /**
     * Set map.
     */
    public function setMap($m)
    {
        $this->_map = $this->buildMap($m);

        return true;
    }

    public function buildMap($m)
    {
        $map = [];
        foreach ($m as $k => $v) {
            $fieldMap = $v;
            if (!isset($fieldMap['class'])) {
                $fieldMap['class'] = $this->fieldMapClass;
            }
            $fieldMap['dataSource'] = $this;
            $fieldMap = Yii::createObject($fieldMap);
            $map[] = $fieldMap;
        }

        return $map;
    }

    /**
     * Get map.
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * Get action.
     */
    public function getAction()
    {
        return $this->module->action;
    }
}
