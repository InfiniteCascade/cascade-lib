<?php
namespace cascade\components\storageHandlers;

use Yii;
use infinite\base\collector\CollectedObjectTrait;
use cascade\models\Storage;
use cascade\models\StorageEngine;

abstract class Handler extends \infinite\base\Component implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    public $error;

    abstract public function generateInternal($item);
    abstract public function validate(StorageEngine $engine, $model, $attribute);
    abstract public function handleSave(Storage $storage, $model, $attribute);

    public function generate($item)
    {
        $rendered = $this->generateInternal($item);
        if ($rendered) {
            $this->prepareRendered($rendered, $item);
        }

        return $rendered;
    }

    public function prepareRendered(&$rendered, $item)
    {
    }

    public function hasFile()
    {
        return $this instanceof UploadInterface;
    }

    protected function prepareStorage(StorageEngine $engine)
    {
        $storageClass = Yii::$app->classes['Storage'];

        return $storageClass::startBlank($engine);
    }

    public function afterDelete(StorageEngine $engine, Storage $model)
    {
        return true;
    }

    public function beforeSave(StorageEngine $engine, $model, $attribute)
    {
        $result = false;
        if (($storage = $this->prepareStorage($engine))) {
            $fill = $this->handleSave($storage, $model, $attribute);
            $result = $storage->fillKill($fill);
            if ($result) {
                $model->{$attribute} = $storage->primaryKey;
            }
        }

        return $result;
    }

    public function beforeSetStorage($value)
    {
        return $value;
    }
}
