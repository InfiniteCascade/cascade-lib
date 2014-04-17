<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

use Yii;
use infinite\base\collector\CollectedObjectTrait;
use cascade\models\Storage;
use cascade\models\StorageEngine;

/**
 * Handler [@doctodo write class description for Handler]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Handler extends \infinite\base\Component implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * @var __var_error_type__ __var_error_description__
     */
    public $error;

    /**
     * __method_generateInternal_description__
     * @param __param_item_type__ $item __param_item_description__
     */
    abstract public function generateInternal($item);
    /**
     * __method_validate_description__
     * @param cascade\models\StorageEngine $engine    __param_engine_description__
     * @param __param_model_type__         $model     __param_model_description__
     * @param __param_attribute_type__     $attribute __param_attribute_description__
     */
    abstract public function validate(StorageEngine $engine, $model, $attribute);
    /**
     * __method_handleSave_description__
     * @param cascade\models\Storage   $storage   __param_storage_description__
     * @param __param_model_type__     $model     __param_model_description__
     * @param __param_attribute_type__ $attribute __param_attribute_description__
     */
    abstract public function handleSave(Storage $storage, $model, $attribute);

    /**
     * __method_generate_description__
     * @param  __param_item_type__      $item __param_item_description__
     * @return __return_generate_type__ __return_generate_description__
     */
    public function generate($item)
    {
        $rendered = $this->generateInternal($item);
        if ($rendered) {
            $this->prepareRendered($rendered, $item);
        }

        return $rendered;
    }

    /**
     * __method_prepareRendered_description__
     * @param __param_rendered_type__ $rendered __param_rendered_description__
     * @param __param_item_type__     $item     __param_item_description__
     */
    public function prepareRendered(&$rendered, $item)
    {
    }

    /**
     * __method_hasFile_description__
     * @return __return_hasFile_type__ __return_hasFile_description__
     */
    public function hasFile()
    {
        return $this instanceof UploadInterface;
    }

    /**
     * __method_prepareStorage_description__
     * @param  cascade\models\StorageEngine   $engine __param_engine_description__
     * @return __return_prepareStorage_type__ __return_prepareStorage_description__
     */
    protected function prepareStorage(StorageEngine $engine)
    {
        $storageClass = Yii::$app->classes['Storage'];

        return $storageClass::startBlank($engine);
    }

    /**
     * __method_afterDelete_description__
     * @param  cascade\models\StorageEngine $engine __param_engine_description__
     * @param  cascade\models\Storage       $model  __param_model_description__
     * @return __return_afterDelete_type__  __return_afterDelete_description__
     */
    public function afterDelete(StorageEngine $engine, Storage $model)
    {
        return true;
    }

    /**
     * __method_beforeSave_description__
     * @param  cascade\models\StorageEngine $engine    __param_engine_description__
     * @param  __param_model_type__         $model     __param_model_description__
     * @param  __param_attribute_type__     $attribute __param_attribute_description__
     * @return __return_beforeSave_type__   __return_beforeSave_description__
     */
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

    /**
     * __method_beforeSetStorage_description__
     * @param  __param_value_type__             $value __param_value_description__
     * @return __return_beforeSetStorage_type__ __return_beforeSetStorage_description__
     */
    public function beforeSetStorage($value)
    {
        return $value;
    }
}
