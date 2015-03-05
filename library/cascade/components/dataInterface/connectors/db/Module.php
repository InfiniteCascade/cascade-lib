<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use cascade\components\dataInterface\connectors\generic\Module as BaseModule;
use infinite\base\exceptions\Exception;
use Yii;
use yii\helpers\Inflector;

/**
 * Module [[@doctodo class_description:cascade\components\dataInterface\connectors\db\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    /**
     * @var [[@doctodo var_type:dataSourceClass]] [[@doctodo var_description:dataSourceClass]]
     */
    public $dataSourceClass = 'cascade\components\dataInterface\connectors\db\DataSource';

    /**
     * @var [[@doctodo var_type:dbConfig]] [[@doctodo var_description:dbConfig]]
     */
    public $dbConfig = [];

    /**
     * @var [[@doctodo var_type:_db]] [[@doctodo var_description:_db]]
     */
    protected $_db;

    /**
     * [[@doctodo method_description:dataSources]].
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
     * Get data source.
     *
     * @return [[@doctodo return_type:getDataSource]] [[@doctodo return_description:getDataSource]]
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
     * Get foreign object.
     *
     * @return [[@doctodo return_type:getForeignObject]] [[@doctodo return_description:getForeignObject]]
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
     * Get foreign model.
     *
     * @return [[@doctodo return_type:getForeignModel]] [[@doctodo return_description:getForeignModel]]
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
     * Get foreign models config.
     *
     * @return [[@doctodo return_type:getForeignModelsConfig]] [[@doctodo return_description:getForeignModelsConfig]]
     */
    public function getForeignModelsConfig()
    {
        return [];
    }

    /**
     * Get foreign model config.
     *
     * @return [[@doctodo return_type:getForeignModelConfig]] [[@doctodo return_description:getForeignModelConfig]]
     */
    public function getForeignModelConfig($tableName, $modelName)
    {
        $config = ['class' => Model::className()];
        if (isset($this->foreignModelsConfig[$modelName])) {
            $config = array_merge($config, $this->foreignModelsConfig[$modelName]);
        }
        $config['modelName'] = $modelName;
        $config['tableName'] = $tableName;
        $config['interface'] = $this;

        return $config;
    }

    /**
     * Get foreign model name.
     *
     * @return [[@doctodo return_type:getForeignModelName]] [[@doctodo return_description:getForeignModelName]]
     */
    public function getForeignModelName($tableName)
    {
        return Inflector::singularize(Inflector::id2camel($tableName, '_'));
    }

    /**
     * Get foreign models.
     *
     * @return [[@doctodo return_type:getForeignModels]] [[@doctodo return_description:getForeignModels]]
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
     * Get db.
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:getDb]] [[@doctodo return_description:getDb]]
     *
     */
    public function getDb()
    {
        if (is_null($this->_db)) {
            if (!isset($this->dbConfig['class'])) {
                $this->dbConfig['class'] = 'cascade\components\dataInterface\connectors\db\Connection';
            }
            $this->_db = Yii::createObject($this->dbConfig);
            $this->_db->open();
        }
        if (empty($this->_db) || !$this->_db->isActive) {
            throw new Exception("Unable to connect to foreign database.");
        }

        return $this->_db;
    }
}
