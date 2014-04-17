<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use Yii;
use cascade\components\db\ActiveRecord;

/**
 * DataSource [@doctodo write class description for DataSource]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class DataSource extends \infinite\base\Component
{
    const EVENT_LOAD_FOREIGN_DATA_ITEMS = 0x01;
    const EVENT_LOAD_LOCAL_DATA_ITEMS = 0x02;

    /**
     * @var __var_fieldMapClass_type__ __var_fieldMapClass_description__
     */
    public $fieldMapClass = 'cascade\\components\\dataInterface\\FieldMap';
    /**
     * @var __var_dataItemClass_type__ __var_dataItemClass_description__
     */
    public $dataItemClass = 'cascade\\components\\dataInterface\\DataItem';
    /**
     * @var __var_searchClass_type__ __var_searchClass_description__
     */
    public $searchClass = 'cascade\\components\\dataInterface\\Search';

    /**
     * @var __var_keyGenerator_type__ __var_keyGenerator_description__
     */
    public $keyGenerator;
    /**
     * @var __var_debug_type__ __var_debug_description__
     */
    public $debug = false;
    /**
     * @var __var_lazyForeign_type__ __var_lazyForeign_description__
     */
    public $lazyForeign = true;
    /**
     * @var __var_lazyLocal_type__ __var_lazyLocal_description__
     */
    public $lazyLocal = true;
    /**
     * @var __var_childOnly_type__ __var_childOnly_description__
     */
    public $childOnly = false;
    /**
     * @var __var_postProcess_type__ __var_postProcess_description__
     */
    public $postProcess = false;

    /**
     * @var __var_ignoreForeign_type__ __var_ignoreForeign_description__
     */
    public $ignoreForeign = false;
    /**
     * @var __var_ignoreLocal_type__ __var_ignoreLocal_description__
     */
    public $ignoreLocal = false;

    /**
     * @var __var_baseAttributes_type__ __var_baseAttributes_description__
     */
    public $baseAttributes = [];

    /**
     * @var __var__localModel_type__ __var__localModel_description__
     */
    protected $_localModel;
    /**
     * @var __var__foreignModel_type__ __var__foreignModel_description__
     */
    protected $_foreignModel;
    /**
     * @var __var__map_type__ __var__map_description__
     */
    protected $_map;
    /**
     * @var __var__settings_type__ __var__settings_description__
     */
    protected $_settings;
    /**
     * @var __var__search_type__ __var__search_description__
     */
    protected $_search;

    /**
     * @var __var__foreignDataItems_type__ __var__foreignDataItems_description__
     */
    protected $_foreignDataItems;
    /**
     * @var __var__localDataItems_type__ __var__localDataItems_description__
     */
    protected $_localDataItems;

    /**
     * @var __var_name_type__ __var_name_description__
     */
    public $name;
    /**
     * @var __var_module_type__ __var_module_description__
     */
    public $module;

    /**
     * @var __var__countTotal_type__ __var__countTotal_description__
     */
    protected $_countTotal;
    /**
     * @var __var__countRemaining_type__ __var__countRemaining_description__
     */
    protected $_countRemaining;

    /**
     * @var __var_defaultSettings_type__ __var_defaultSettings_description__
     */
    static $defaultSettings = [
        'direction' => 'to_local', // to_local, to_foreign, both
        'update' => true,
        'create' => true,
        'deleteLocal' => false,
        'deleteForeign' => false,
        'foreignPullParams' => [],
        'universalKey' => false
    ];

    // abstract public function handleLocal($action, DataItem $dataItem, DataItem $parent = null);

    /**
     * __method_isReady_description__
     * @return __return_isReady_type__ __return_isReady_description__
     */
    public function isReady()
    {
        return isset($this->localModel) && isset($this->foreignModel);
    }

    /**
     * __method_clearCaches_description__
     */
    public function clearCaches()
    {
        ActiveRecord::clearCache();
    }

    /**
     * __method_getTotal_description__
     * @return __return_getTotal_type__ __return_getTotal_description__
     */
    public function getTotal()
    {
        if (!$this->isReady()) { return 0; }
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
     * __method_getDone_description__
     * @return __return_getDone_type__ __return_getDone_description__
     */
    public function getDone()
    {
        return $this->total - $this->remaining;
    }

    /**
     * __method_getDummyLocalModel_description__
     * @return __return_getDummyLocalModel_type__ __return_getDummyLocalModel_description__
     */
    public function getDummyLocalModel()
    {
        $localModelClass = $this->localModel;

        return new $localModelClass;
    }

    /**
     * __method_getRemaining_description__
     * @return __return_getRemaining_type__ __return_getRemaining_description__
     */
    public function getRemaining()
    {
        if (is_null($this->_countRemaining)) {
            $this->_countRemaining = $this->total;
        }

        return $this->_countRemaining;
    }

    /**
     * __method_reduceRemaining_description__
     * @param cascade\components\dataInterface\DataItem $dataItem __param_dataItem_description__
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

        $this->_countRemaining = $this->remaining - $n;
        $this->action->reduceRemaining($n);
    }

    /**
     * __method_getForeignDataItems_description__
     * @return __return_getForeignDataItems_type__ __return_getForeignDataItems_description__
     */
    public function getForeignDataItems()
    {
        if (!isset($this->_foreignDataItems)) {
            $this->trigger(self::EVENT_LOAD_FOREIGN_DATA_ITEMS);
        }

        return $this->_foreignDataItems;
    }

    /**
     * __method_getLocalDataItems_description__
     * @return __return_getLocalDataItems_type__ __return_getLocalDataItems_description__
     */
    public function getLocalDataItems()
    {
        if (!isset($this->_localDataItems)) {
            $this->trigger(self::EVENT_LOAD_LOCAL_DATA_ITEMS);
        }

        return $this->_localDataItems;
    }

    /**
     * __method_getHandledLocalDataItems_description__
     * @return __return_getHandledLocalDataItems_type__ __return_getHandledLocalDataItems_description__
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
     * __method_run_description__
     * @return __return_run_type__ __return_run_description__
     */
    public function run()
    {
        if (!$this->isReady()) { return false; }
        $action = $this->action;
        $this->settings = $action->settings;

        if (in_array($this->settings['direction'], ['to_local', 'both'])) {
            // start foreign
            foreach ($this->foreignDataItems as $dataItem) {
                $dataItem->handler->handle();
                $this->clearCaches();
            }
        }

        if (in_array($this->settings['direction'], ['to_foreign', 'both'])) {
            // start local
            foreach ($this->localDataItems as $dataItem) {
                $dataItem->handler->handle();
                $this->clearCaches();
            }
        }

        return true;
    }

    /**
     * __method_getDataInterface_description__
     * @return __return_getDataInterface_type__ __return_getDataInterface_description__
     */
    public function getDataInterface()
    {
        return $this->_dataInterface;
    }

    /**
     * __method_setSettings_description__
     * @param  __param_settings_type__     $settings __param_settings_description__
     * @return __return_setSettings_type__ __return_setSettings_description__
     */
    public function setSettings($settings)
    {
        if (is_null($this->_settings)) {
            $this->_settings = self::$defaultSettings;
        }
        if (!is_array($settings)) { return true; }
        $this->_settings = array_merge($this->_settings, $settings);

        return true;
    }

    /**
     * __method_getSettings_description__
     * @return __return_getSettings_type__ __return_getSettings_description__
     */
    public function getSettings()
    {
        if (is_null($this->_settings)) {
            $this->settings = [];
        }

        return $this->_settings;
    }

    /**
     * __method_getSearch_description__
     * @return __return_getSearch_type__ __return_getSearch_description__
     */
    public function getSearch()
    {
        return $this->_search;
    }

    /**
     * __method_setSearch_description__
     * @param __param_value_type__ $value __param_value_description__
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
     * __method_getLocalModel_description__
     * @return __return_getLocalModel_type__ __return_getLocalModel_description__
     */
    public function getLocalModel()
    {
        return $this->_localModel;
    }

    /**
     * __method_setLocalModel_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setLocalModel($value)
    {
        $this->_localModel = ActiveRecord::parseModelAlias($value);
    }

    /**
     * __method_setForeignModel_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setForeignModel($value)
    {
        $this->_foreignModel = $value;
    }

    /**
     * __method_getForeignModel_description__
     * @return __return_getForeignModel_type__ __return_getForeignModel_description__
     */
    public function getForeignModel()
    {
        return $this->_foreignModel;
    }

    /**
     * __method_setMap_description__
     * @param  __param_m_type__       $m __param_m_description__
     * @return __return_setMap_type__ __return_setMap_description__
     */
    public function setMap($m)
    {
        foreach ($m as $k => $v) {
            $fieldMap = $v;
            if (!isset($fieldMap['class'])) {
                $fieldMap['class'] = $this->fieldMapClass;
            }
            $fieldMap['dataSource'] = $this;
            $fieldMap = Yii::createObject($fieldMap);
            $this->_map[] = $fieldMap;
        }

        return true;
    }

    /**
     * __method_getMap_description__
     * @return __return_getMap_type__ __return_getMap_description__
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * __method_getAction_description__
     * @return __return_getAction_type__ __return_getAction_description__
     */
    public function getAction()
    {
        return $this->module->action;
    }
}
