<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use cascade\components\dataInterface\Action;
use cascade\components\dataInterface\Module as BaseModule;
use infinite\action\Action as BaseAction;
use Yii;

/**
 * Module [[@doctodo class_description:cascade\components\dataInterface\connectors\generic\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    /**
     * @var [[@doctodo var_type:dataSourceClass]] [[@doctodo var_description:dataSourceClass]]
     */
    public $dataSourceClass = 'cascade\components\dataInterface\connectors\generic\DataSource';

    /**
     * @var [[@doctodo var_type:_models]] [[@doctodo var_description:_models]]
     */
    protected $_models;

    /**
     * @var [[@doctodo var_type:_dataSources]] [[@doctodo var_description:_dataSources]]
     */
    protected $_dataSources;
    /**
     * @var [[@doctodo var_type:_action]] [[@doctodo var_description:_action]]
     */
    protected $_action;

    /**
     * Get foreign object.
     */
    abstract public function getForeignObject($foreignModelClass, $foreignPrimaryKey);
    /**
     * Get foreign model.
     */
    abstract public function getForeignModel($model);

    /**
     * @inheritdoc
     */
    public function run(BaseAction $action)
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

            $task = $action->status->addTask($id, $prefix . ' ' . $source->descriptor);
            $source->task = $task;
            $source->prepareTask();
            $taskSets[] = [
                'task' => $task,
                'dataSource' => $source,
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

    /**
     * [[@doctodo method_description:beforeRun]].
     *
     * @return [[@doctodo return_type:beforeRun]] [[@doctodo return_description:beforeRun]]
     */
    public function beforeRun()
    {
        return true;
    }

    /**
     * [[@doctodo method_description:afterRun]].
     *
     * @return [[@doctodo return_type:afterRun]] [[@doctodo return_description:afterRun]]
     */
    public function afterRun()
    {
        return true;
    }

    /**
     * Get local object.
     *
     * @return [[@doctodo return_type:getLocalObject]] [[@doctodo return_description:getLocalObject]]
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

    /**
     * Get data source.
     *
     * @return [[@doctodo return_type:getDataSource]] [[@doctodo return_description:getDataSource]]
     */
    public function getDataSource($dataSourceName)
    {
        if (isset($this->dataSources[$dataSourceName])) {
            return $this->dataSources[$dataSourceName];
        }

        return false;
    }

    /**
     * Get data sources.
     *
     * @return [[@doctodo return_type:getDataSources]] [[@doctodo return_description:getDataSources]]
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
                if (empty($dataSource['foreignModel'])) {
                    continue;
                }
                $this->_dataSources[$foreignModel] = Yii::createObject(array_merge(['module' => $this], $dataSource));
            }
        }

        return $this->_dataSources;
    }

    /**
     * Get local data source.
     *
     * @return [[@doctodo return_type:getLocalDataSource]] [[@doctodo return_description:getLocalDataSource]]
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
     * Get foreign data source.
     *
     * @return [[@doctodo return_type:getForeignDataSource]] [[@doctodo return_description:getForeignDataSource]]
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
     * Get action.
     *
     * @return [[@doctodo return_type:getAction]] [[@doctodo return_description:getAction]]
     */
    public function getAction()
    {
        if (is_null($this->_action)) {
            $this->_action = new Action();
        }

        return $this->_action;
    }
}
