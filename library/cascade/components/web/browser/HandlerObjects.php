<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\browser;

use Yii;

/**
 * HandlerObjects [@doctodo write class description for HandlerObjects]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class HandlerObjects extends \infinite\web\browser\Handler
{
    public $bundleClass = 'cascade\\components\\web\\browser\\Bundle';
    protected $_dataSource;

    public function getDataSource()
    {
        if (is_null($this->_dataSource)) {
            $typeItem = Yii::$app->collectors['types']->getOne($this->instructions['type']);
            if (!$typeItem || !($type = $typeItem->object)) { return $this->_dataSource = false; }
            $primaryModel = $type->primaryModel;
            if (isset($this->instructions['parent'])) {
                $registryClass = Yii::$app->classes['Registry'];
                $object = $registryClass::getObject($this->instructions['parent']);
                if (!$object) { return $this->_dataSource = false; }
                $this->_dataSource = $object->queryChildObjects($primaryModel, [], []);
            } else {
                $this->_dataSource = $primaryModel::find();
            }
            $dummyModel = new $primaryModel;
            $sortOptions = array_values($dummyModel->sortOptions);
            if ($this->filterQuery) {
                $primaryModel::simpleSearchTermQuery($this->_dataSource, $this->filterQuery);
            } elseif (isset($sortOptions[0])) {
                $this->_dataSource->orderBy($sortOptions[0]);
            }
        }

        return $this->_dataSource;
    }

    /**
    * @inheritdoc
    **/
    public function getTotal()
    {
        if (!$this->dataSource) {
            return false;
        }

        return $this->dataSource->count();
    }

    /**
    * @inheritdoc
    **/
    public function getItems()
    {
        $instructions = $this->instructions;
        if (!$this->dataSource) {
            return false;
        }
        $dataSource = clone $this->dataSource;
        if (!$this->filterQuery) {
            $dataSource->limit($this->bundle->limit);
            $dataSource->offset($this->bundle->offset);
        }
        $items = [];
        foreach ($dataSource->all() as $object) {
            $items[] = [
                'type' => 'object',
                'objectType' => $object->objectType->systemId,
                'id' => $object->primaryKey,
                'descriptor' => $object->descriptor,
                'hasChildren' => !empty($object->objectTypeItem->children),
                'isSelectable' => $instructions['modules'] === false || in_array($object->objectType->systemId, $instructions['modules'])
            ];
        }

        return $items;
    }
}
