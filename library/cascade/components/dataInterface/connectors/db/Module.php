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
 * Module base module for database data interfaces.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    /**
     * @var string class to use for module's data source
     */
    public $dataSourceClass = 'cascade\components\dataInterface\connectors\db\DataSource';

    /**
     * @var array database connection configuration
     */
    public $dbConfig = [];

    /**
     * @var Connection database connection to be used
     */
    protected $_db;

    /**
     * Returns the data sources to be used in the interface.
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
     * @param [[@doctodo param_type:tableName]] $tableName [[@doctodo param_description:tableName]]
     *
     * @return DataSource|bool the data source based on {$tableName} or false if not found
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
     * @param [[@doctodo param_type:foreignModelClass]] $foreignModelClass [[@doctodo param_description:foreignModelClass]]
     * @param [[@doctodo param_type:foreignPrimaryKey]] $foreignPrimaryKey [[@doctodo param_description:foreignPrimaryKey]]
     *
     * @return Model|bool foreign data model or false if failed or not found
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
     * @param [[@doctodo param_type:model]] $model [[@doctodo param_description:model]]
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
     * @param [[@doctodo param_type:tableName]] $tableName [[@doctodo param_description:tableName]]
     * @param [[@doctodo param_type:modelName]] $modelName [[@doctodo param_description:modelName]]
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
     * @param [[@doctodo param_type:tableName]] $tableName [[@doctodo param_description:tableName]]
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
