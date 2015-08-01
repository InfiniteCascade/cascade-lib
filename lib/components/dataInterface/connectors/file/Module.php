<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use cascade\components\dataInterface\connectors\generic\Module as BaseModule;
use Yii;
use yii\helpers\Inflector;

/**
 * Module [[@doctodo class_description:cascade\components\dataInterface\connectors\file\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends BaseModule
{
    /**
     * @var [[@doctodo var_type:_sourceFiles]] [[@doctodo var_description:_sourceFiles]]
     */
    protected $_sourceFiles = [];
    /**
     * @var [[@doctodo var_type:dataSourceClass]] [[@doctodo var_description:dataSourceClass]]
     */
    public $dataSourceClass = 'cascade\components\dataInterface\connectors\file\DataSource';
    /**
     * @var [[@doctodo var_type:sourceFileClass]] [[@doctodo var_description:sourceFileClass]]
     */
    public $sourceFileClass = 'cascade\components\dataInterface\connectors\file\SourceFile';

    /**
     * @var [[@doctodo var_type:_dataSources]] [[@doctodo var_description:_dataSources]]
     */
    protected $_dataSources;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        //\d($this->dataSources);exit;
        return true;
    }

    /**
     * Set source files.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setSourceFiles($value)
    {
        foreach ($value as $key => $settings) {
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

    /**
     * Get source files.
     *
     * @return [[@doctodo return_type:getSourceFiles]] [[@doctodo return_description:getSourceFiles]]
     */
    public function getSourceFiles()
    {
        return $this->_sourceFiles;
    }

    /**
     * [[@doctodo method_description:loadForeignModels]].
     */
    public function loadForeignModels()
    {
    }

    /**
     * [[@doctodo method_description:packageNode]].
     *
     * @param [[@doctodo param_type:ous]]  $ous  [[@doctodo param_description:ous]]
     * @param [[@doctodo param_type:node]] $node [[@doctodo param_description:node]]
     *
     * @return [[@doctodo return_type:packageNode]] [[@doctodo return_description:packageNode]]
     */
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

        if (empty($object['id'])) {
            return false;
        }
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
     * @param [[@doctodo param_type:sourceFile]] $sourceFile [[@doctodo param_description:sourceFile]]
     * @param [[@doctodo param_type:modelName]]  $modelName  [[@doctodo param_description:modelName]]
     *
     * @return [[@doctodo return_type:getForeignModelConfig]] [[@doctodo return_description:getForeignModelConfig]]
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
     * Get foreign models.
     *
     * @return [[@doctodo return_type:getForeignModels]] [[@doctodo return_description:getForeignModels]]
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

    /**
     * @inheritdoc
     */
    public function getForeignObject($foreignModelClass, $foreignPrimaryKey)
    {
        \d($foreignPrimaryKey);

        return false;
    }
}
