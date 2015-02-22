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
        if (!$this->beforeRun()) {
            // @todo add something to action log
            return false;
        }
        $total = 0;
        $taskSets = [];
        foreach ($this->dataSources as $source) {
            $id = md5(uniqid(rand(), true));
            if ($source->settings['direction'] === 'to_local') {
                $prefix = 'Importing';
            } elseif ($source->settings['direction'] === 'to_foreign') {
                $prefix = 'Exporting';
            } else {
                $prefix = 'Syncing';
            }
            
            $task = $action->status->addTask($id, $prefix .' '. $source->name);
            $source->task = $task;
            $source->prepareTask();
            $taskSets[] = [
                'task' => $task,
                'dataSource' => $source
            ];
        }

        foreach ($taskSets as $taskSet) {
            $task = $taskSet['task'];
            $dataSource = $taskSet['dataSource'];
            $dataSource->run();
        }
        if (!$this->afterRun()) {
            // @todo add action log
            return false;
        }
        return true;
    }

    public function beforeRun()
    {
        return true;
    }

    public function afterRun()
    {
        return true;
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
                if (is_numeric($foreignModel) || isset($dataSources['foreignModel'])) {
                    if (!isset($dataSources['foreignModel'])) {
                        continue;
                    }
                    $foreignModel = $dataSources['foreignModel'];
                    unset($dataSources['foreignModel']);
                }
                if (!isset($dataSource['class'])) {
                    $dataSource['class'] = $this->dataSourceClass;
                }
                $dataSource['name'] = $foreignModel;
                $dataSource['foreignModel'] = $this->getForeignModel($foreignModel);
                if (empty($dataSource['foreignModel'])) { continue; }
                $this->_dataSources[$foreignModel] = Yii::createObject(array_merge(['module' => $this], $dataSource));
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