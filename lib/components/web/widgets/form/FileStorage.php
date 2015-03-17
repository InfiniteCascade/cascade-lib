<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\form;

use cascade\components\web\widgets\Widget;
use cascade\models\StorageEngine;
use canis\base\exceptions\Exception;
use canis\helpers\Html;

/**
 * FileStorage [[@doctodo class_description:cascade\components\web\widgets\form\FileStorage]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class FileStorage extends Widget
{
    /**
     * @var [[@doctodo var_type:item]] [[@doctodo var_description:item]]
     */
    public $item;

    /**
     * @inheritdoc
     */
    public function generateContent()
    {
        $renderedStorageEngines = [];
        $storageEngines = StorageEngine::find()->setAction('read')->all();
        foreach ($storageEngines as $key => $storageEngine) {
            $storageHandler = $storageEngine->storageHandler;
            if (!$storageHandler
                || !($renderedStorageEngines[$storageEngine->primaryKey] = $this->renderItem($storageEngine))
                ) {
                unset($storageEngines[$key]);
            }
        }
        $storageEngines = array_values($storageEngines);

        if (empty($storageEngines)) {
            throw new Exception("No storage engines are available to you.");
        }
        if (count($storageEngines) === 1) {
            $storageEngine = $storageEngines[0];
            $storageHandler = $storageEngine->storageHandler;

            return $renderedStorageEngines[$storageEngine->primaryKey];
        } else {
            return "@todo implement multiple storage engines widget";
        }
    }

    /**
     * [[@doctodo method_description:prepareItem]].
     *
     * @param [[@doctodo param_type:engine]] $engine [[@doctodo param_description:engine]]
     *
     * @return [[@doctodo return_type:prepareItem]] [[@doctodo return_description:prepareItem]]
     */
    public function prepareItem($engine)
    {
        $item = clone $this->item;
        $item->inputOptions['data-engine'] = $engine->primaryKey;
        Html::addCssClass($item->inputOptions, 'storage-field');

        return $item;
    }

    /**
     * [[@doctodo method_description:renderItem]].
     *
     * @param [[@doctodo param_type:storageEngine]] $storageEngine [[@doctodo param_description:storageEngine]]
     *
     * @return [[@doctodo return_type:renderItem]] [[@doctodo return_description:renderItem]]
     */
    public function renderItem($storageEngine)
    {
        $item = $this->prepareItem($storageEngine);
        $rendered = $storageEngine->storageHandler->object->generate($item);
        if (!$rendered) {
            return false;
        }
        $hiddenItem = clone $item;
        $hiddenItem->attribute = Html::changeAttribute($hiddenItem->attribute, 'storageEngine');
        $item->model->storageEngine = $storageEngine->primaryKey;
        $rendered .= Html::activeHiddenInput($item->model, $hiddenItem->attribute, $item->inputOptions);

        return $rendered;
    }

    /**
     * 	@inheritdoc
     */
    public function run()
    {
        return $this->generateContent();
    }
}
