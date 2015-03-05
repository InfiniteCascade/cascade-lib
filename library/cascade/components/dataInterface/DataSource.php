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
 * DataSource [[@doctodo class_description:cascade\components\dataInterface\DataSource]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataSource extends \infinite\base\Component
{
    const EVENT_LOAD_FOREIGN_DATA_ITEMS = 0x01;
    const EVENT_LOAD_LOCAL_DATA_ITEMS = 0x02;

    /**
     * @var [[@doctodo var_type:task]] [[@doctodo var_description:task]]
     */
    public $task;
    /**
     * @var [[@doctodo var_type:taskWeight]] [[@doctodo var_description:taskWeight]]
     */
    public $taskWeight = 1;

    /**
     * @var [[@doctodo var_type:fieldMapClass]] [[@doctodo var_description:fieldMapClass]]
     */
    public $fieldMapClass = 'cascade\components\dataInterface\FieldMap';
    /**
     * @var [[@doctodo var_type:dataItemClass]] [[@doctodo var_description:dataItemClass]]
     */
    public $dataItemClass = 'cascade\components\dataInterface\DataItem';
    /**
     * @var [[@doctodo var_type:searchClass]] [[@doctodo var_description:searchClass]]
     */
    public $searchClass = 'cascade\components\dataInterface\Search';

    /**
     * @var [[@doctodo var_type:keyGenerator]] [[@doctodo var_description:keyGenerator]]
     */
    public $keyGenerator;
    /**
     * @var [[@doctodo var_type:debug]] [[@doctodo var_description:debug]]
     */
    public $debug = false;
    /**
     * @var [[@doctodo var_type:lazyForeign]] [[@doctodo var_description:lazyForeign]]
     */
    public $lazyForeign = true;
    /**
     * @var [[@doctodo var_type:lazyLocal]] [[@doctodo var_description:lazyLocal]]
     */
    public $lazyLocal = true;
    /**
     * @var [[@doctodo var_type:childOnly]] [[@doctodo var_description:childOnly]]
     */
    public $childOnly = false;
    /**
     * @var [[@doctodo var_type:postProcess]] [[@doctodo var_description:postProcess]]
     */
    public $postProcess = false;

    /**
     * @var [[@doctodo var_type:ignoreForeign]] [[@doctodo var_description:ignoreForeign]]
     */
    public $ignoreForeign = false;
    /**
     * @var [[@doctodo var_type:ignoreLocal]] [[@doctodo var_description:ignoreLocal]]
     */
    public $ignoreLocal = false;

    /**
     * @var [[@doctodo var_type:baseAttributes]] [[@doctodo var_description:baseAttributes]]
     */
    public $baseAttributes = [];

    /**
     * @var [[@doctodo var_type:_localModel]] [[@doctodo var_description:_localModel]]
     */
    protected $_localModel;
    /**
     * @var [[@doctodo var_type:_foreignModel]] [[@doctodo var_description:_foreignModel]]
     */
    protected $_foreignModel;
    /**
     * @var [[@doctodo var_type:_map]] [[@doctodo var_description:_map]]
     */
    protected $_map;
    /**
     * @var [[@doctodo var_type:_settings]] [[@doctodo var_description:_settings]]
     */
    protected $_settings;
    /**
     * @var [[@doctodo var_type:_search]] [[@doctodo var_description:_search]]
     */
    protected $_search;

    /**
     * @var [[@doctodo var_type:_foreignDataItems]] [[@doctodo var_description:_foreignDataItems]]
     */
    protected $_foreignDataItems;
    /**
     * @var [[@doctodo var_type:_localDataItems]] [[@doctodo var_description:_localDataItems]]
     */
    protected $_localDataItems;

    /**
     * @var [[@doctodo var_type:name]] [[@doctodo var_description:name]]
     */
    public $name;
    /**
     * @var [[@doctodo var_type:module]] [[@doctodo var_description:module]]
     */
    public $module;

    /**
     * @var [[@doctodo var_type:_countTotal]] [[@doctodo var_description:_countTotal]]
     */
    protected $_countTotal;
    /**
     * @var [[@doctodo var_type:_countRemaining]] [[@doctodo var_description:_countRemaining]]
     */
    protected $_countRemaining;

    /**
     * @var [[@doctodo var_type:defaultSettings]] $defaultSettings [[@doctodo var_description:defaultSettings]]
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
     * [[@doctodo method_description:isReady]].
     *
     * @return [[@doctodo return_type:isReady]] [[@doctodo return_description:isReady]]
     */
    public function isReady()
    {
        return isset($this->localModel) && isset($this->foreignModel);
    }

    /**
     * [[@doctodo method_description:clearCaches]].
     */
    public function clearCaches()
    {
        ActiveRecord::clearCache();
        \yii\caching\Dependency::resetReusableData();
        \cascade\components\types\Relationship::clearCache();
    }

    /**
     * Get descriptor.
     *
     * @return [[@doctodo return_type:getDescriptor]] [[@doctodo return_description:getDescriptor]]
     */
    public function getDescriptor()
    {
        return Inflector::titleize($this->name, true);
    }

    /**
     * Get total.
     *
     * @return [[@doctodo return_type:getTotal]] [[@doctodo return_description:getTotal]]
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
     *
     * @return [[@doctodo return_type:getDone]] [[@doctodo return_description:getDone]]
     */
    public function getDone()
    {
        return $this->total - $this->remaining;
    }

    /**
     * Get dummy local model.
     *
     * @return [[@doctodo return_type:getDummyLocalModel]] [[@doctodo return_description:getDummyLocalModel]]
     */
    public function getDummyLocalModel()
    {
        $localModelClass = $this->localModel;

        return new $localModelClass();
    }

    /**
     * Get remaining.
     *
     * @return [[@doctodo return_type:getRemaining]] [[@doctodo return_description:getRemaining]]
     */
    public function getRemaining()
    {
        if (is_null($this->_countRemaining)) {
            $this->_countRemaining = $this->total;
        }

        return $this->_countRemaining;
    }

    /**
     * [[@doctodo method_description:reduceRemaining]].
     *
     * @param cascade\components\dataInterface\DataItem $dataItem [[@doctodo param_description:dataItem]]
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
     *
     * @return [[@doctodo return_type:getForeignDataItems]] [[@doctodo return_description:getForeignDataItems]]
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
     *
     * @return [[@doctodo return_type:getLocalDataItems]] [[@doctodo return_description:getLocalDataItems]]
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
     *
     * @return [[@doctodo return_type:getHandledLocalDataItems]] [[@doctodo return_description:getHandledLocalDataItems]]
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

    /**
     * [[@doctodo method_description:universalFilter]].
     *
     * @return [[@doctodo return_type:universalFilter]] [[@doctodo return_description:universalFilter]]
     */
    public function universalFilter($value)
    {
        return $value;
    }

    /**
     * [[@doctodo method_description:prepareTask]].
     */
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
     * [[@doctodo method_description:run]].
     *
     * @return [[@doctodo return_type:run]] [[@doctodo return_description:run]]
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
     *
     * @return [[@doctodo return_type:getDataInterface]] [[@doctodo return_description:getDataInterface]]
     */
    public function getDataInterface()
    {
        return $this->_dataInterface;
    }

    /**
     * Set settings.
     *
     * @return [[@doctodo return_type:setSettings]] [[@doctodo return_description:setSettings]]
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
     *
     * @return [[@doctodo return_type:getSettings]] [[@doctodo return_description:getSettings]]
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
     *
     * @return [[@doctodo return_type:getSearch]] [[@doctodo return_description:getSearch]]
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
     *
     * @return [[@doctodo return_type:getLocalModel]] [[@doctodo return_description:getLocalModel]]
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
     *
     * @return [[@doctodo return_type:getForeignModel]] [[@doctodo return_description:getForeignModel]]
     */
    public function getForeignModel()
    {
        return $this->_foreignModel;
    }

    /**
     * Set map.
     *
     * @return [[@doctodo return_type:setMap]] [[@doctodo return_description:setMap]]
     */
    public function setMap($m)
    {
        $this->_map = $this->buildMap($m);

        return true;
    }

    /**
     * [[@doctodo method_description:buildMap]].
     *
     * @return [[@doctodo return_type:buildMap]] [[@doctodo return_description:buildMap]]
     */
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
     *
     * @return [[@doctodo return_type:getMap]] [[@doctodo return_description:getMap]]
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * Get action.
     *
     * @return [[@doctodo return_type:getAction]] [[@doctodo return_description:getAction]]
     */
    public function getAction()
    {
        return $this->module->action;
    }
}
