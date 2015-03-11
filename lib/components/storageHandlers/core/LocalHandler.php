<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\storageHandlers\core;

use cascade\models\Storage;
use cascade\models\StorageEngine;
use teal\base\exceptions\Exception;
use teal\base\FileInterface;
use teal\helpers\Date;
use Yii;
use yii\helpers\FileHelper;

/**
 * LocalHandler [[@doctodo class_description:cascade\components\storageHandlers\core\LocalHandler]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class LocalHandler extends \cascade\components\storageHandlers\Handler
    implements \cascade\components\storageHandlers\UploadInterface
{
    /**
     * @var [[@doctodo var_type:localFileClass]] [[@doctodo var_description:localFileClass]]
     */
    public $localFileClass = 'teal\base\File';
    /**
     * @var [[@doctodo var_type:bucketFormat]] [[@doctodo var_description:bucketFormat]]
     */
    public $bucketFormat = '{year}.{month}';
    /**
     * @var [[@doctodo var_type:_baseDir]] [[@doctodo var_description:_baseDir]]
     */
    protected $_baseDir;

    /**
     * @inheritdoc
     */
    public function beforeSetStorage($value)
    {
        if (is_array($value) && isset($value['tempName'])) {
            if (!isset($value['class'])) {
                $value['class'] = $this->localFileClass;
            }
            if (!isset($value['size'])) {
                $value['size'] = filesize($value['tempName']);
            }
            if (!isset($value['type'])) {
                $value['type'] = FileHelper::getMimeType($value['tempName']);
            }
            if (!isset($value['name'])) {
                $value['name'] = basename($vale['tempName']);
            }
            $value = Yii::createObject($value);
        }

        return $value;
    }

    /**
     * [[@doctodo method_description:buildKey]].
     *
     * @return [[@doctodo return_type:buildKey]] [[@doctodo return_description:buildKey]]
     */
    public function buildKey()
    {
        $keyVariables = $this->keyVariables;
        $keyParts = explode('.', $this->bucketFormat);
        foreach ($keyParts as &$part) {
            $part = strtr($part, $keyVariables);
        }

        return $keyParts;
    }

    /**
     * Get key variables.
     *
     * @return [[@doctodo return_type:getKeyVariables]] [[@doctodo return_description:getKeyVariables]]
     */
    public function getKeyVariables()
    {
        $vars = [];
        $time = Date::time();
        $vars['{year}'] = Date::date("Y", $time);
        $vars['{month}'] = Date::date("m", $time);
        $vars['{day}'] = Date::date("d", $time);
        $vars['{hour}'] = Date::date("H", $time);
        $vars['{minute}'] = Date::date("i", $time);

        return $vars;
    }

    /**
     * @inheritdoc
     */
    public function serve(Storage $storage)
    {
        $path = $this->getPath($storage);
        if (!file_exists($path)) {
            return false;
        }
        Yii::$app->response->sendFile($path, trim($storage->file_name), $storage->type);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function handleSave(Storage $storage, $model, $attribute)
    {
        return $this->handleUpload($storage, $model, $attribute);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(StorageEngine $engine, Storage $model)
    {
        $path = $this->getPath($model);
        if (file_exists($path)) {
            @unlink($path);
        }

        return true;
    }

    /**
     * Get path.
     *
     * @param cascade\models\Storage $model [[@doctodo param_description:model]]
     *
     * @return [[@doctodo return_type:getPath]] [[@doctodo return_description:getPath]]
     */
    public function getPath(Storage $model)
    {
        $baseKey = explode('.', $model->storage_key);
        $dirPath = $this->baseDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $baseKey);
        if (!is_dir($dirPath)) {
            @mkdir($dirPath, 0755, true);
        }
        if (!is_dir($dirPath)) {
            $this->error = 'Unable to create storage directory';

            return false;
        }

        return $dirPath . DIRECTORY_SEPARATOR . $model->primaryKey;
    }

    /**
     * [[@doctodo method_description:handleUpload]].
     *
     * @param cascade\models\Storage            $storage   [[@doctodo param_description:storage]]
     * @param [[@doctodo param_type:model]]     $model     [[@doctodo param_description:model]]
     * @param [[@doctodo param_type:attribute]] $attribute [[@doctodo param_description:attribute]]
     *
     * @return [[@doctodo return_type:handleUpload]] [[@doctodo return_description:handleUpload]]
     */
    public function handleUpload(Storage $storage, $model, $attribute)
    {
        if (!($model->{$attribute} instanceof FileInterface)) {
            return true;
        }
        $package = [];
        $baseKey = $this->buildKey();
        $package['storage_key'] = implode('.', $baseKey);
        $dirPath = $this->baseDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $baseKey);
        if (!is_dir($dirPath)) {
            @mkdir($dirPath, 0755, true);
        }
        if (!is_dir($dirPath)) {
            $this->error = 'Unable to create storage directory';

            return false;
        }
        $path = $dirPath . DIRECTORY_SEPARATOR . $storage->primaryKey;
        $file = $model->{$attribute};
        if ($file->saveAs($path) && file_exists($path)) {
            $package['file_name'] = $file->name;
            $package['size'] = $file->size;
            $package['type'] = FileHelper::getMimeType($path);

            return $package;
        }
        var_dump($path);

        return false;
    }

    /**
     * @inheritdoc
     */
    public function validate(StorageEngine $engine, $model, $attribute)
    {
        $errorMessage = "No file was uploaded!";
        if ($model->{$attribute} instanceof FileInterface) {
            if (!$model->{$attribute}->hasError) {
                return true;
            } else {
                $errorMessage = 'An error occurred during file transport.';
            }
        } elseif (!$model->isNewRecord) {
            return true;
        }
        $model->addError($attribute, $errorMessage);

        return false;
    }

    /**
     * Set base dir.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:setBaseDir]] [[@doctodo return_description:setBaseDir]]
     *
     */
    public function setBaseDir($value)
    {
        $value = Yii::getAlias($value);
        if (!is_dir($value)) {
            @mkdir($value, 0755, true);
            if (!is_dir($value)) {
                throw new Exception("Unable to set local storage base directory: {$value}");
            }
        }

        return $this->_baseDir = $value;
    }

    /**
     * Get base dir.
     *
     * @return [[@doctodo return_type:getBaseDir]] [[@doctodo return_description:getBaseDir]]
     */
    public function getBaseDir()
    {
        return $this->_baseDir;
    }

    /**
     * @inheritdoc
     */
    public function generateInternal($item)
    {
        return $item->fileInput();
    }
}
