<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use Yii;
use yii\helpers\Inflector;
use infinite\base\exceptions\Exception;
use cascade\components\dataInterface\Action;
use cascade\components\dataInterface\Module as BaseModule;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    /**
     * @var __var_dataSourceClass_type__ __var_dataSourceClass_description__
     */
    public $dataSourceClass = 'cascade\\components\\dataInterface\\connectors\\generic\\DataSource';

    /**
     * @var __var__models_type__ __var__models_description__
     */
    protected $_models;


    protected $_dataSources;
    /**
     * @var __var__action_type__ __var__action_description__
     */
    protected $_action;

    abstract public function getForeignObject($foreignModelClass, $foreignPrimaryKey);
    abstract public function getForeignModel($model);

    /**
    * @inheritdoc
     */
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
        $this->afterRun();
    }

    public function afterRun()
    {

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
     * Get local object
     * @param __param_localModelClass_type__   $localModelClass   __param_localModelClass_description__
     * @param __param_foreignPrimaryKey_type__ $foreignPrimaryKey __param_foreignPrimaryKey_description__
     * @return __return_getLocalObject_type__   __return_getLocalObject_description__
     */
    public function getLocalObject($localModelClass, $foreignPrimaryKey)
    {
        $dataSource = $this->getLocalDataSource($localModelClass);
        if (is_array($foreignPrimaryKey) && isset($foreignPrimaryKey['localId'])) {
            $registryClass = Yii::$app->classes['Registry'];
            return $registryClass::getObject($foreignPrimaryKey['localId'], false);
        }
        if ($dataSource && ($foreignDataItem = $dataSource->getForeignDataItem($foreignPrimaryKey))) {
            return $foreignDataItem->handle(true);
        }

        return false;
    }

    public function getDataSource($dataSourceName)
    {
        if (isset($this->dataSources[$dataSourceName])) {
            return $this->dataSources[$dataSourceName];
        }
        return false;
    }

    /**
     * Get data sources
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
     * Get local data source
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
     * Get foreign data source
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
     * Get action
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