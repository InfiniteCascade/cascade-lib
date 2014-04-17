<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use Yii;
use yii\helpers\Inflector;
use infinite\base\exceptions\Exception;
use cascade\components\dataInterface\Action;
use cascade\components\dataInterface\Module as BaseModule;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Module extends BaseModule
{
    /**
     * @var __var_dataSourceClass_type__ __var_dataSourceClass_description__
     */
    public $dataSourceClass = 'cascade\\components\\dataInterface\\connectors\\db\\DataSource';
    /**
     * @var __var_dbConfig_type__ __var_dbConfig_description__
     */
    public $dbConfig = [];
    /**
     * @var __var__action_type__ __var__action_description__
     */
    protected $_action;

    /**
     * @var __var__models_type__ __var__models_description__
     */
    protected $_models;
    /**
     * @var __var__db_type__ __var__db_description__
     */
    protected $_db;
    /**
     * @var __var__dataSources_type__ __var__dataSources_description__
     */
    protected $_dataSources;

    /**
     * __method_dataSources_description__
     */
    abstract public function dataSources();

    // public function getKeyTranslation(Model $foreignObject) {
    // 	$key = $this->generateKey($foreignObject);
    // 	if ($this->settings['universalKey']) {
    // 		return KeyTranslation::get($key);
    // 	} else {
    // 		return KeyTranslation::get($key, $this->dataInterface->interfaceItem->interfaceObject);
    // 	}
    // }

    /**
    * @inheritdoc
    **/
    public function run(Action $action)
    {
        $this->_action = $action;
        $total = 0;
        foreach ($this->dataSources as $source) {
            $total += $source->total;
        }

        $action->progressTotal = $total;
        foreach ($this->dataSources as $source) {
            if ($source->settings['direction'] === 'to_local') {
                $prefix = 'Importing';
            } elseif ($source->settings['direction'] === 'to_foreign') {
                $prefix = 'Exporting';
            } else {
                $prefix = 'Syncing';
            }
            $action->progressPrefix = "{$prefix} {$source->name}...";
            $source->run();
        }
    }

    /**
     * __method_getDataSource_description__
     * @param __param_tableName_type__      $tableName __param_tableName_description__
     * @return __return_getDataSource_type__ __return_getDataSource_description__
     */
    public function getDataSource($tableName)
    {
        $model = $this->getForeignModelName($tableName);
        if (isset($this->dataSources[$model])) {
            return $this->dataSources[$model];
        }
        foreach ($this->dataSources as $dataSource) {
            if ($dataSource->foreignModel->tableName === $tableName) {
                return $dataSource;
            }
        }

        return false;
    }

    /**
     * __method_getDataSources_description__
     * @return __return_getDataSources_type__ __return_getDataSources_description__
     */
    public function getDataSources()
    {
        if (is_null($this->_dataSources)) {
            $this->_dataSources = [];
            foreach ($this->dataSources() as $foreignModel => $dataSource) {
                if (!isset($dataSource['class'])) {
                    $dataSource['class'] = $this->dataSourceClass;
                }
                $dataSource['name'] = $foreignModel;
                $dataSource['foreignModel'] = $this->getForeignModel($foreignModel);
                $dataSource['module'] = $this;
                $this->_dataSources[$foreignModel] = Yii::createObject($dataSource);
            }
        }

        return $this->_dataSources;
    }

    /**
     * __method_updateLocalObject_description__
     * @param __param_relatedType_type__        $relatedType       __param_relatedType_description__
     * @param __param_foreignPrimaryKey_type__  $foreignPrimaryKey __param_foreignPrimaryKey_description__
     * @param __param_valueMap_type__           $valueMap          __param_valueMap_description__
     * @param __param_fieldMap_type__           $fieldMap          __param_fieldMap_description__
     * @param __param_localModel_type__         $localModel        __param_localModel_description__
     * @return __return_updateLocalObject_type__ __return_updateLocalObject_description__
     */
    public function updateLocalObject($relatedType, $foreignPrimaryKey, $valueMap, $fieldMap, $localModel)
    {
        $localModelClass = $relatedType->primaryModel;
        // @todo eventually we'll probably take some keys out of this
        $searchMap = $valueMap;
        if (isset($fieldMap->searchFields) && is_array($fieldMap->searchFields)) {
            foreach ($searchMap as $field => $value) {
                if (!in_array($field, $fieldMap->searchFields)) {
                    unset($searchMap[$field]);
                }
            }
        }

        $fieldParts = explode(':', $fieldMap->localField);
        if ($fieldParts[0] === 'child') {
            $currentRelationsFunction = 'child';
        } else {
            $currentRelationsFunction = 'parent';
        }
        // first, lets see if it exists
        $relatedObject = null;
        $currentRelation = false;
        if (!empty($localModel) && !$localModel->isNewRecord) {
            $test = $localModel->{$currentRelationsFunction}($relatedType->primaryModel, [], ['where' => $searchMap, 'disableAccessCheck' => 1]);
            if ($test) {
                $relatedObject = $test;
                $currentRelation = true;
            }
        }

        if (empty($relatedObject)) {
            $relatedClass = $relatedType->primaryModel;
            $relatedObject = new $relatedClass;
        }
        $relatedObject->attributes = $valueMap;
        if ($relatedObject->save()) {
            return $relatedObject;
        } else {
            \d($relatedObject); exit;

            return false;
        }
    }

    /**
     * __method_getLocalObject_description__
     * @param __param_localModelClass_type__   $localModelClass   __param_localModelClass_description__
     * @param __param_foreignPrimaryKey_type__ $foreignPrimaryKey __param_foreignPrimaryKey_description__
     * @return __return_getLocalObject_type__   __return_getLocalObject_description__
     */
    public function getLocalObject($localModelClass, $foreignPrimaryKey)
    {
        $dataSource = $this->getLocalDataSource($localModelClass);
        if ($dataSource && ($foreignDataItem = $dataSource->getForeignDataItem($foreignPrimaryKey))) {
            return $foreignDataItem->handle(true);
        }

        return false;
    }

    /**
     * __method_getForeignObject_description__
     * @param __param_foreignModelClass_type__ $foreignModelClass __param_foreignModelClass_description__
     * @param __param_foreignPrimaryKey_type__ $foreignPrimaryKey __param_foreignPrimaryKey_description__
     * @return __return_getForeignObject_type__ __return_getForeignObject_description__
     */
    public function getForeignObject($foreignModelClass, $foreignPrimaryKey)
    {
        $dataSource = $this->getForeignDataSource($foreignModelClass);
        if ($dataSource && ($foreignDataItem = $dataSource->getForeignDataItem($foreignPrimaryKey))) {
            return $foreignDataItem->handle(true);
        }

        return false;
    }

    /**
     * __method_getLocalDataSource_description__
     * @param __param_localModelClass_type__     $localModelClass __param_localModelClass_description__
     * @return __return_getLocalDataSource_type__ __return_getLocalDataSource_description__
     */
    public function getLocalDataSource($localModelClass)
    {
        foreach ($this->dataSources as $dataSource) {
            if ($dataSource->localModel === $localModelClass) {
                return $dataSource;
            }
        }

        return false;
    }

    /**
     * __method_getForeignDataSource_description__
     * @param __param_foreignModelClass_type__     $foreignModelClass __param_foreignModelClass_description__
     * @return __return_getForeignDataSource_type__ __return_getForeignDataSource_description__
     */
    public function getForeignDataSource($foreignModelClass)
    {
        foreach ($this->dataSources as $dataSource) {
            if ($dataSource->foreignModel->modelName === $foreignModelClass) {
                return $dataSource;
            }
        }

        return false;
    }

    /**
     * __method_getForeignModel_description__
     * @param __param_model_type__            $model __param_model_description__
     * @return __return_getForeignModel_type__ __return_getForeignModel_description__
     */
    public function getForeignModel($model)
    {
        $models = $this->foreignModels;
        if (isset($models[$model])) {
            return $models[$model];
        }

        return false;
    }

    /**
     * __method_getForeignModelsConfig_description__
     * @return __return_getForeignModelsConfig_type__ __return_getForeignModelsConfig_description__
     */
    public function getForeignModelsConfig()
    {
        return [];
    }

    /**
     * __method_getForeignModelConfig_description__
     * @param __param_tableName_type__              $tableName __param_tableName_description__
     * @param __param_modelName_type__              $modelName __param_modelName_description__
     * @return __return_getForeignModelConfig_type__ __return_getForeignModelConfig_description__
     */
    public function getForeignModelConfig($tableName, $modelName)
    {
        $config = ['class' => 'cascade\\components\\dataInterface\\connectors\\db\\Model'];
        if (isset($this->foreignModelsConfig[$modelName])) {
            $config = array_merge($config, $this->foreignModelsConfig[$modelName]);
        }
        $config['modelName'] = $modelName;
        $config['tableName'] = $tableName;
        $config['interface'] = $this;

        return $config;
    }

    /**
     * __method_getForeignModelName_description__
     * @param __param_tableName_type__            $tableName __param_tableName_description__
     * @return __return_getForeignModelName_type__ __return_getForeignModelName_description__
     */
    public function getForeignModelName($tableName)
    {
        return Inflector::singularize(Inflector::id2camel($tableName, '_'));
    }

    /**
     * __method_getForeignModels_description__
     * @return __return_getForeignModels_type__ __return_getForeignModels_description__
     */
    public function getForeignModels()
    {
        if (is_null($this->_models)) {
            $this->_models = [];
            foreach ($this->db->schema->getTableNames() as $tableName) {
                $modelName = $this->getForeignModelName($tableName);
                $this->_models[$modelName] = Yii::createObject($this->getForeignModelConfig($tableName, $modelName));
            }
        }

        return $this->_models;
    }

    /**
     * __method_getDb_description__
     * @return __return_getDb_type__ __return_getDb_description__
     * @throws Exception __exception_Exception_description__
     */
    public function getDb()
    {
        if (is_null($this->_db)) {
            if (!isset($this->dbConfig['class'])) {
                $this->dbConfig['class'] = 'cascade\\components\\dataInterface\\connectors\\db\\Connection';
            }
            $this->_db = Yii::createObject($this->dbConfig);
            $this->_db->open();
        }
        if (empty($this->_db) || !$this->_db->isActive) {
            throw new Exception("Unable to connect to foreign database.");
        }

        return $this->_db;
    }

    /**
     * __method_getAction_description__
     * @return __return_getAction_type__ __return_getAction_description__
     */
    public function getAction()
    {
        if (is_null($this->_action)) {
            $this->_action = new Action;
        }

        return $this->_action;
    }
}
