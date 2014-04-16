<?php
namespace cascade\components\dataInterface;

use Yii;
use cascade\models\DataInterface;

abstract class DataItem extends \infinite\base\Component
{
    public $dataSource;
    public $isForeign = true;

    protected $_pairedDataItem;
    protected $_handledDataItem = false;
    protected $_foreignObject;
    public $foreignPrimaryKey;

    protected $_localObject;
    public $localPrimaryKey;

    const EVENT_LOAD_FOREIGN_OBJECT = 0x01;
    const EVENT_LOAD_LOCAL_OBJECT = 0x02;

    public function init()
    {
        $this->on(self::EVENT_LOAD_LOCAL_OBJECT, [$this, 'searchLocalObject']);
        parent::init();
    }

    protected function searchLocalObject($event)
    {
        if (isset($this->foreignObject) && !isset($this->_localObject) && isset($this->dataSource->search)) {
            if (($localObject = $this->dataSource->search->searchLocal($this)) && !empty($localObject)) {
                $this->localObject = $localObject;
            }
        }

        return true;
    }

    public function clean()
    {
        if (isset($this->foreignObject)) {
            $this->foreignPrimaryKey = $this->foreignObject->primaryKey;
            $this->foreignObject = null;
        }
        if (isset($this->localObject)) {
            $this->localPrimaryKey = $this->localObject->primaryKey;
            $this->localObject = null;
        }
        Yii::getLogger()->flush();
    }

    public function getId()
    {
        if ($this->isForeign) {
            if (isset($this->foreignPrimaryKey)) {
                return $this->foreignPrimaryKey;
            }
        } else {
            if (isset($this->localPrimaryKey)) {
                return $this->localPrimaryKey;
            }
        }
        if (isset($this->primaryObject)) {
            return $this->primaryObject->primaryKey;
        }

        return null;
    }

    public function handle($fromParent = false, $baseAttributes = [])
    {
        if ($this->handledDataItem) {
            if ($this->isForeign) {
                $object = $this->localObject;
            } else {
                $object = $this->foreignObject;
            }
            if (!empty($baseAttributes)) {
                foreach ($baseAttributes as $key => $value) {
                    $object->{$key} = $value;
                }
                if (!$object->save()) {
                    return false;
                }
            }

            return $object;
        }
        if ($fromParent || !$this->dataSource->childOnly) {
            if ($this->isForeign) {
                // handle local to foreign
                $result = $this->handler->handleForeign($baseAttributes);
            } else {
                $result = $this->handler->handleLocal($baseAttributes);
            }
        } else {
            $result = true;
        }
        if (is_null($result)) {
            $this->handledDataItem = true;
        } elseif ($result) {
            if (is_object($result)) {
                $this->handledDataItem = true;
            }
            if ($this->dataSource->postProcess) {
                call_user_func($this->dataSource->postProcess, $this);
            }

            return $result;
        }

        return false;
    }

    public function getIgnoreForeignObject()
    {
        return $this->testIgnore($this->foreignObject, $this->dataSource->ignoreForeign);
    }

    public function getIgnoreLocalObject()
    {
        return $this->testIgnore($this->localObject, $this->dataSource->ignoreLocal);
    }

    protected function testIgnore($object, $ignore)
    {
        if (!$object) {
            return true;
        }
        if ($ignore === true) {
            return true;
        } elseif (is_array($ignore)) {
            foreach ($ignore as $key => $value) {
                $objectValue = $object->{$key};
                if (is_object($value)) {
                    if ($value->test($objectValue)) {
                        return true;
                    }
                } elseif (is_array($value)) {
                    if (in_array($objectValue, $value)) {
                        return true;
                    }
                } else {
                    if ($objectValue === $value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function handleLocal($baseAttributes = [])
    {
        return false;
    }

    protected function handleForeign($baseAttributes = [])
    {
        return false;
    }

    public function getHandler()
    {
        if ($this->pairedDataItem) {
            if (!isset($this->primaryObject)) {
                return $this->pairedDataItem;
            } elseif (isset($this->companionObject)) {
                return static::getHandlingObject($this, $this->pairedDataItem);
            }
        }

        return $this;
    }

    public function getHandlingComparison()
    {
        return false;
    }

    public static function getHandlingObject($a, $b)
    {
        $handlingA = $a->handlingComparison;
        $handlingB = $b->handlingComparison;

        if (!$handlingB) {
            return $a;
        }
        if ($handlingA !== false && $handlingB !== false) {
            if ($handlingA > $handlingB) {
                return $a;
            } else {
                return $b;
            }
        }

        return $a;
    }

    public function getPrimaryObject()
    {
        if ($this->isForeign) {
            return $this->foreignObject;
        } else {
            return $this->localObject;
        }
    }

    public function getCompanionObject()
    {
        if ($this->isForeign) {
            return $this->localObject;
        } else {
            return $this->foreignObject;
        }
    }

    public function setCompanionObject($value)
    {
        if ($this->isForeign) {
            return $this->localObject = $value;
        } else {
            return $this->foreignObject = $value;
        }
    }

    public function getCompanionId()
    {
        if ($this->isForeign && isset($this->foreignPrimaryKey)) {
            return $this->foreignPrimaryKey;
        } elseif (!$this->isForeign && isset($this->localPrimaryKey)) {
            return $this->localPrimaryKey;
        }
        if (isset($this->companionObject)) {
            return $this->companionObject->primaryKey;
        }

        return null;
    }

    public function setPairedDataItem(DataItem $value)
    {
        $this->_pairedDataItem = $value;
        if (!isset($this->_localObject) && isset($value->localObject)) {
            $this->localObject = $value->localObject;
        }

        if (!isset($this->_foreignObject) && isset($value->foreignObject)) {
            $this->foreignObject = $value->foreignObject;
        }

        if ($value->handledDataItem) {
            $this->handledDataItem = $value->handledDataItem;
        }
    }

    public function getPairedDataItem()
    {
        return $this->_pairedDataItem;
    }

    public function setHandledDataItem($value)
    {
        if (isset($this->_pairedDataItem)) {
            $this->pairedDataItem->handledDataItem = $value;
        }
        if (!$this->_handledDataItem && $value) {
            $this->dataSource->reduceRemaining($this);
        }
        $this->clean();

        return $this->_handledDataItem = $value;
    }

    public function getHandledDataItem()
    {
        return $this->_handledDataItem;
    }

    public function getForeignObject()
    {
        if (is_null($this->_foreignObject)) {
            $this->trigger(self::EVENT_LOAD_FOREIGN_OBJECT);
        }

        return $this->_foreignObject;
    }

    public function setForeignObject($value)
    {
        $this->_foreignObject = $value;
    }

    public function getLocalObject()
    {
        if (is_null($this->_localObject)) {
            $this->trigger(self::EVENT_LOAD_LOCAL_OBJECT);
        }

        return $this->_localObject;
    }

    public function setLocalObject($value)
    {
        $this->_localObject = $value;
    }

    public function getAction()
    {
        return $this->dataSource->action;
    }

    public function getModule()
    {
        return $this->dataSource->module;
    }
}
