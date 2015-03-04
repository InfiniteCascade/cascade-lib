<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use Yii;
use yii\helpers\Inflector;
use infinite\base\exceptions\Exception;
use cascade\components\dataInterface\Action;
use cascade\components\dataInterface\connectors\generic\Module as BaseModule;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    protected $_sourceFiles = [];
    /**
     * @var __var_dataSourceClass_type__ __var_dataSourceClass_description__
     */
    public $dataSourceClass = 'cascade\components\dataInterface\connectors\file\DataSource';
    public $sourceFileClass = 'cascade\components\dataInterface\connectors\file\SourceFile';
    

    /**
     * @var __var__dataSources_type__ __var__dataSources_description__
     */
    protected $_dataSources;

    public function beforeRun()
    {
        //\d($this->dataSources);exit;
        return true;
    }

    public function setSourceFiles($value)
    {
        foreach ($value as $key => $settings)
        {
            if ($settings === false) {
                $this->_sourceFiles[$key] = false;
                continue;
            }
            if (!isset($settings['class'])) {
                $settings['class'] = $this->sourceFileClass;
            }
            $settings['id'] = $key;
            $this->_sourceFiles[$key] = Yii::createObject($settings);
        }
    }

    public function getSourceFiles()
    {
        return $this->_sourceFiles;
    }

    public function loadForeignModels()
    {
        
    }

    public function packageNode($ous, $node)
    {
        $attributes = $node->getAttributes();
        $p = ['object' => null, 'parents' => []];
        $object = [];
        $object['id'] = isset($attributes['objectGUID']) ? md5(implode(' ', $attributes['objectGUID']->getValues())) : null;
        $object['first_name'] = isset($attributes['givenName']) ? implode(' ', $attributes['givenName']->getValues()) : null;
        $object['last_name'] = isset($attributes['sn']) ? implode(' ', $attributes['sn']->getValues()) : null;
        $object['title'] = isset($attributes['title']) ? implode(' ', $attributes['title']->getValues()) : null;
        $object['email'] = isset($attributes['mail']) ? strtolower(implode(' ', $attributes['mail']->getValues())) : null;
        $object['phone_number'] = isset($attributes['telephoneNumber']) ? strtolower(implode(' ', $attributes['telephoneNumber']->getValues())) : null;
        $object['username'] = isset($attributes['sAMAccountName']) ? strtolower(implode(' ', $attributes['sAMAccountName']->getValues())) : null;
       
        if (empty($object['id'])) { return false; }
        $dataSources = $this->dataSources;
        $model = $dataSources['Individual']->registerReturnForeignModel($object);
        if (!$model) {
            return false;
        }
        $object['object_individual_id'] = $model;
        $userModel = $dataSources['User']->registerReturnForeignModel($object);

        //$dataSources['User']->registerReturnForeignModel($object);
        $p['object'] = $model;
        $p['parents'] = $this->discoverParents($ous, $node);
        foreach ($p['parents'] as $parent) {
            $parent->foreignObject->addChild($model);
        }
        return $p;
    }


    public function getForeignModelsConfig()
    {
        return [];
    }

    /**
     * Get foreign model config
     * @param __param_tableName_type__              $tableName __param_tableName_description__
     * @param __param_modelName_type__              $modelName __param_modelName_description__
     * @return __return_getForeignModelConfig_type__ __return_getForeignModelConfig_description__
     */
    public function getForeignModelConfig($sourceFile, $modelName)
    {
        $config = ['class' => Model::className()];
        if (isset($this->foreignModelsConfig[$modelName])) {
            $config = array_merge($config, $this->foreignModelsConfig[$modelName]);
        }
        $config['modelName'] = $modelName;
        $config['sourceFile'] = $sourceFile;
        $config['interface'] = $this;
        return $config;
    }

    /**
     * Get foreign model name
     * @param __param_tableName_type__            $tableName __param_tableName_description__
     * @return __return_getForeignModelName_type__ __return_getForeignModelName_description__
     */
    public function getForeignModelName($tableName)
    {
        return Inflector::singularize(Inflector::id2camel($tableName, '_'));
    }
    
    /**
     * Get foreign model
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
     * Get foreign models
     * @return __return_getForeignModels_type__ __return_getForeignModels_description__
     */
    public function getForeignModels()
    {
        if (is_null($this->_models)) {
            $this->_models = [];
            foreach ($this->sourceFiles as $sourceFile) {
                $modelName = $this->getForeignModelName($sourceFile->id);
                $this->_models[$modelName] = Yii::createObject($this->getForeignModelConfig($sourceFile, $modelName));
            }
        }

        return $this->_models;
    }

    public function getForeignObject($foreignModelClass, $foreignPrimaryKey)
    {
        \d($foreignPrimaryKey);

        return false;
    }
}
