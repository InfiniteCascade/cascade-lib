<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use Exception;
use canis\base\language\Verb;

/**
 * DataItem [[@doctodo class_description:cascade\components\dataInterface\connectors\generic\DataItem]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataItem extends \cascade\components\dataInterface\DataItem
{
    /**
     * @var [[@doctodo var_type:_isLoadingForeignObject]] [[@doctodo var_description:_isLoadingForeignObject]]
     */
    protected $_isLoadingForeignObject = false;
    /**
     * @var [[@doctodo var_type:_isLoadingLocalObject]] [[@doctodo var_description:_isLoadingLocalObject]]
     */
    protected $_isLoadingLocalObject = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_LOAD_FOREIGN_OBJECT, [$this, 'loadForeignObject']);
        $this->on(self::EVENT_LOAD_LOCAL_OBJECT, [$this, 'loadLocalObject']);
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function handleForeign($baseAttributes = [])
    {
        if ($this->ignoreForeignObject) {
            return;
        }

        // foreign to local

        // find or start up local object
        $localModel = $this->dataSource->localModel;
        if (!isset($this->localObject)) {
            $this->localObject = new $localModel();
        }
        $isNewRecord = $this->localObject->isNewRecord;
        $actionVerb = new Verb('create');
        if (!$isNewRecord) {
            $actionVerb = new Verb('update');
        }
        // $this->localObject->auditAgent = $this->module->collectorItem->interfaceObject->primaryKey;
        $this->localObject->auditDataInterface = $this->module->collectorItem->interfaceObject->primaryKey;

        $attributes = $this->dataSource->buildLocalAttributes($this->foreignObject, $this->localObject);
        if (empty($attributes)) {
            return false;
        }

        if ($this->localModelError) {
            $this->dataSource->task->addError('Unable to match local ' . $this->dataSource->descriptor . ' object', ['attributes' => $attributes]);

            return false;
        }

        $relations = [];
        // load local object
        foreach ($attributes as $key => $value) {
            $this->localObject->{$key} = $value;
        }

        foreach (array_merge($this->dataSource->baseAttributes, $baseAttributes) as $key => $value) {
            $this->localObject->{$key} = $value;
        }

        // save local object
        if (!$this->localObject->save()) {
            $this->dataSource->task->addError('Unable to ' . $actionVerb->getSimplePresent(false) . ' local ' . $this->dataSource->descriptor . ' object: ' . $this->localObject->descriptor, ['errors' => $this->localObject->errors, 'attributes' => $this->localObject->attributes]);

            return false;
        }
        $dirtyAttributes = $this->localObject->getDirtyAttributes();
        $oldAttributes = $this->localObject->getOldAttributes();

        if ($this->localObject->getBehavior('Auditable') !== null) {
            foreach ($this->localObject->getBehavior('Auditable')->ignoreAttributes as $ignore) {
                unset($dirtyAttributes[$ignore]);
            }
        }

        if ($this->localObject->getBehavior('Date') !== null) {
            $oldAttributes = $this->localObject->getBehavior('Date')->convertToDatabaseDate($oldAttributes);
            $dirtyAttributes = $this->localObject->getBehavior('Date')->convertToDatabaseDate($dirtyAttributes);
            foreach ($dirtyAttributes as $key => $value) {
                if (isset($oldAttributes[$key]) && $value === $oldAttributes[$key]) {
                    unset($dirtyAttributes[$key]);
                }
            }
        }

        if ($isNewRecord || !empty($dirtyAttributes)) {
            $infoData = [];
            if (!$isNewRecord) {
                // @todo use auditable to ignore certain fields
                $infoData = ['newValues' => $dirtyAttributes, 'oldValues' => []];

                foreach ($dirtyAttributes as $key => $newValue) {
                    $oldValue = null;
                    if (isset($oldAttributes[$key])) {
                        $oldValue = $oldAttributes[$key];
                    }
                    $infoData['oldValues'][$key] = $oldValue;
                }
            }
            $this->dataSource->task->addInfo($actionVerb->getPast(true) . ' local ' . $this->dataSource->descriptor . ' object: ' . $this->localObject->descriptor, $infoData);
        }

        // save foreign key map
        if (!$this->dataSource->saveKeyTranslation($this->foreignObject, $this->localObject)) {
            throw new Exception("Unable to save key translation!");
        }

        // loop through children
        // some interfaces ask the foreignObject for children
        foreach ($this->foreignObject->children as $table => $children) {
            $dataSource = $this->module->getDataSource($table);
            if (empty($dataSource) || !$dataSource->isReady()) {
                continue;
            }
            foreach ($children as $childId) {
                // let the handler figure it out
                if (!($dataItem = $dataSource->getForeignDataItem($childId))) {
                    continue;
                }
                $childLocalObject = $dataItem->handle(true, ['indirectObject' => $this->localObject, 'relationModels' => [['parent_object_id' => $this->localObject->primaryKey]]]);
            }
        }
        foreach ($this->foreignChildren as $modelName => $children) {
            $dataSource = $this->module->getDataSource($modelName);
            if (empty($dataSource) || !$dataSource->isReady()) {
                continue;
            }
            foreach ($children as $relationSet) {
                // let the handler figure it out
                if (!($dataItem = $dataSource->getForeignDataItem($relationSet['foreignId']))) {
                    continue;
                }
                $relationSet['parent_object_id'] = $this->localObject->primaryKey;
                unset($relationSet['foreignId']);
                $childLocalObject = $dataItem->handle(true, ['indirectObject' => $this->localObject, 'relationModels' => [$relationSet]]);
            }
        }
        foreach ($this->foreignParents as $modelName => $parents) {
            $dataSource = $this->module->getDataSource($modelName);
            if (empty($dataSource) || !$dataSource->isReady()) {
                \d($dataSource);
                \d("$modelName isn't ready");
                exit;
                continue;
            }
            foreach ($parents as $relationSet) {
                // let the handler figure it out
                if (!($dataItem = $dataSource->getForeignDataItem($relationSet['foreignId']))) {
                    continue;
                }
                $relationSet['child_object_id'] = $this->localObject->primaryKey;
                unset($relationSet['foreignId']);
                $parentLocalObject = $dataItem->handle(true, ['indirectObject' => $this->localObject, 'relationModels' => [$relationSet]]);
            }
        }

        return $this->localObject;
    }

    /**
     * Get foreign parents.
     *
     * @return [[@doctodo return_type:getForeignParents]] [[@doctodo return_description:getForeignParents]]
     */
    public function getForeignParents()
    {
        $parents = [];
        foreach ($this->dataSource->foreignParentKeys as $keySet) {
            $model = $keySet['foreignModel'];
            unset($keySet['foreignModel']);
            if (!empty($this->foreignObject->{$keySet['foreignId']})) {
                $keySet['foreignId'] = $this->foreignObject->{$keySet['foreignId']};
                if (!isset($parents[$model])) {
                    $parents[$model] = [];
                }
                $parents[$model][] = $keySet;
            }
        }

        return $parents;
    }

    /**
     * Get foreign children.
     *
     * @return [[@doctodo return_type:getForeignChildren]] [[@doctodo return_description:getForeignChildren]]
     */
    public function getForeignChildren()
    {
        $children = [];
        foreach ($this->dataSource->foreignChildKeys as $keySet) {
            $model = $keySet['foreignModel'];
            unset($keySet['foreignModel']);
            if (!empty($this->foreignObject->{$keySet['foreignId']})) {
                $keySet['foreignId'] = $this->foreignObject->{$keySet['foreignId']};
                if (!isset($children[$model])) {
                    $children[$model] = [];
                }
                $children[$model][] = $keySet;
            }
        }

        return $children;
    }

    /**
     * [[@doctodo method_description:loadForeignObject]].
     */
    abstract protected function loadForeignObject();

    /**
     * [[@doctodo method_description:loadLocalObject]].
     */
    abstract protected function loadLocalObject();
}
