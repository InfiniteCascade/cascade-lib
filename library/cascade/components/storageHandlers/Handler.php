<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

use cascade\models\Storage;
use cascade\models\StorageEngine;
use infinite\base\collector\CollectedObjectTrait;
use Yii;

/**
 * Handler [[@doctodo class_description:cascade\components\storageHandlers\Handler]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Handler extends \infinite\base\Component implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * @var [[@doctodo var_type:error]] [[@doctodo var_description:error]]
     */
    public $error;

    /**
     * [[@doctodo method_description:generateInternal]].
     */
    abstract public function generateInternal($item);
    /**
     * [[@doctodo method_description:validate]].
     *
     * @param cascade\models\StorageEngine $engine [[@doctodo param_description:engine]]
     */
    abstract public function validate(StorageEngine $engine, $model, $attribute);
    /**
     * [[@doctodo method_description:handleSave]].
     *
     * @param cascade\models\Storage $storage [[@doctodo param_description:storage]]
     */
    abstract public function handleSave(Storage $storage, $model, $attribute);

    /**
     * [[@doctodo method_description:serve]].
     *
     * @param cascade\models\Storage $storage [[@doctodo param_description:storage]]
     */
    abstract public function serve(Storage $storage);

    /**
     * [[@doctodo method_description:generate]].
     *
     * @return [[@doctodo return_type:generate]] [[@doctodo return_description:generate]]
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
     * [[@doctodo method_description:prepareRendered]].
     */
    public function prepareRendered(&$rendered, $item)
    {
    }

    /**
     * [[@doctodo method_description:hasFile]].
     *
     * @return [[@doctodo return_type:hasFile]] [[@doctodo return_description:hasFile]]
     */
    public function hasFile()
    {
        return $this instanceof UploadInterface;
    }

    /**
     * [[@doctodo method_description:prepareStorage]].
     *
     * @param cascade\models\StorageEngine $engine [[@doctodo param_description:engine]]
     *
     * @return [[@doctodo return_type:prepareStorage]] [[@doctodo return_description:prepareStorage]]
     */
    protected function prepareStorage(StorageEngine $engine)
    {
        $storageClass = Yii::$app->classes['Storage'];

        return $storageClass::startBlank($engine);
    }

    /**
     * [[@doctodo method_description:afterDelete]].
     *
     * @param cascade\models\StorageEngine $engine [[@doctodo param_description:engine]]
     * @param cascade\models\Storage       $model  [[@doctodo param_description:model]]
     *
     * @return [[@doctodo return_type:afterDelete]] [[@doctodo return_description:afterDelete]]
     */
    public function afterDelete(StorageEngine $engine, Storage $model)
    {
        return true;
    }

    /**
     * [[@doctodo method_description:beforeSave]].
     *
     * @param cascade\models\StorageEngine $engine [[@doctodo param_description:engine]]
     *
     * @return [[@doctodo return_type:beforeSave]] [[@doctodo return_description:beforeSave]]
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
     * [[@doctodo method_description:beforeSetStorage]].
     *
     * @return [[@doctodo return_type:beforeSetStorage]] [[@doctodo return_description:beforeSetStorage]]
     */
    public function beforeSetStorage($value)
    {
        return $value;
    }
}
