<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use Yii;
use yii\helpers\Inflector;
use infinite\base\exceptions\Exception;
use cascade\components\dataInterface\connectors\generic\Module as BaseModule;

/**
 * Module [@doctodo write class description for Module].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    /**
     * @var __var_dataSourceClass_type__ __var_dataSourceClass_description__
     */
    public $dataSourceClass = 'cascade\components\dataInterface\connectors\db\DataSource';

    /**
     * @var __var_dbConfig_type__ __var_dbConfig_description__
     */
    public $dbConfig = [];

    /**
     * @var __var__db_type__ __var__db_description__
     */
    protected $_db;

    /**
     * __method_dataSources_description__.
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
     * @param __param_tableName_type__ $tableName __param_tableName_description__
     *
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
     * Get foreign object.
     *
     * @param __param_foreignModelClass_type__ $foreignModelClass __param_foreignModelClass_description__
     * @param __param_foreignPrimaryKey_type__ $foreignPrimaryKey __param_foreignPrimaryKey_description__
     *
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
     * Get foreign model.
     *
     * @param __param_model_type__ $model __param_model_description__
     *
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
     * Get foreign models config.
     *
     * @return __return_getForeignModelsConfig_type__ __return_getForeignModelsConfig_description__
     */
    public function getForeignModelsConfig()
    {
        return [];
    }

    /**
     * Get foreign model config.
     *
     * @param __param_tableName_type__ $tableName __param_tableName_description__
     * @param __param_modelName_type__ $modelName __param_modelName_description__
     *
     * @return __return_getForeignModelConfig_type__ __return_getForeignModelConfig_description__
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
     * @param __param_tableName_type__ $tableName __param_tableName_description__
     *
     * @return __return_getForeignModelName_type__ __return_getForeignModelName_description__
     */
    public function getForeignModelName($tableName)
    {
        return Inflector::singularize(Inflector::id2camel($tableName, '_'));
    }

    /**
     * Get foreign models.
     *
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
     * Get db.
     *
     * @return __return_getDb_type__ __return_getDb_description__
     *
     * @throws Exception __exception_Exception_description__
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
