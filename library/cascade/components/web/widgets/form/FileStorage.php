<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\form;

use infinite\helpers\Html;
use infinite\base\exceptions\Exception;
use cascade\components\web\widgets\Widget;
use cascade\models\StorageEngine;

/**
 * FileStorage [@doctodo write class description for FileStorage]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class FileStorage extends Widget
{
    public $item;

    /**
    * @inheritdoc
    **/
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

    public function prepareItem($engine)
    {
        $item = clone $this->item;
        $item->inputOptions['data-engine'] = $engine->primaryKey;
        Html::addCssClass($item->inputOptions, 'storage-field');

        return $item;
    }

    public function renderItem($storageEngine)
    {
        $item = $this->prepareItem($storageEngine);
        $rendered = $storageEngine->storageHandler->object->generate($item);
        if (!$rendered) { return false; }
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
