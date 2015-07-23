<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\dataInterface;

use Yii;

/**
 * DataItem [[@doctodo class_description:cascade\components\dataInterface\DataItem]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataItem extends \canis\base\Component
{
    /**
     * @var [[@doctodo var_type:dataSource]] [[@doctodo var_description:dataSource]]
     */
    public $dataSource;
    /**
     * @var [[@doctodo var_type:isForeign]] [[@doctodo var_description:isForeign]]
     */
    public $isForeign = true;

    /**
     * @var [[@doctodo var_type:localModelError]] [[@doctodo var_description:localModelError]]
     */
    public $localModelError = false;
    /**
     * @var [[@doctodo var_type:foreignModelError]] [[@doctodo var_description:foreignModelError]]
     */
    public $foreignModelError = false;

    /**
     * @var [[@doctodo var_type:_pairedDataItem]] [[@doctodo var_description:_pairedDataItem]]
     */
    protected $_pairedDataItem;
    /**
     * @var [[@doctodo var_type:_handledDataItem]] [[@doctodo var_description:_handledDataItem]]
     */
    protected $_handledDataItem = false;
    /**
     * @var [[@doctodo var_type:_foreignObject]] [[@doctodo var_description:_foreignObject]]
     */
    protected $_foreignObject;
    /**
     * @var [[@doctodo var_type:foreignPrimaryKey]] [[@doctodo var_description:foreignPrimaryKey]]
     */
    public $foreignPrimaryKey;

    /**
     * @var [[@doctodo var_type:_localObject]] [[@doctodo var_description:_localObject]]
     */
    protected $_localObject;
    /**
     * @var [[@doctodo var_type:localPrimaryKey]] [[@doctodo var_description:localPrimaryKey]]
     */
    public $localPrimaryKey;

    /**
     * @var [[@doctodo var_type:baseAttributes]] [[@doctodo var_description:baseAttributes]]
     */
    public $baseAttributes = [];

    const EVENT_LOAD_FOREIGN_OBJECT = 0x01;
    const EVENT_LOAD_LOCAL_OBJECT = 0x02;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_LOAD_LOCAL_OBJECT, [$this, 'searchLocalObject']);
        parent::init();
    }

    /**
     * [[@doctodo method_description:searchLocalObject]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     *
     * @return [[@doctodo return_type:searchLocalObject]] [[@doctodo return_description:searchLocalObject]]
     */
    protected function searchLocalObject($event)
    {
        if (isset($this->foreignObject) && !isset($this->_localObject) && isset($this->dataSource->search)) {
            if (($localObject = $this->dataSource->search->searchLocal($this)) && !empty($localObject)) {
                $this->localObject = $localObject;
            }

            if ($localObject === false) {
                $this->localModelError = true;
            }
        }

        return true;
    }

    /**
     * [[@doctodo method_description:clean]].
     */
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

    /**
     * Get id.
     *
     * @return [[@doctodo return_type:getId]] [[@doctodo return_description:getId]]
     */
    public function getId()
    {
        if ($this->isForeign) {
            if (isset($this->foreignPrimaryKey)) {
                return $this->foreignPrimaryKey;
            } elseif (isset($this->foreignObject)) {
                return $this->foreignObject->primaryKey;
            }
        } else {
            if (isset($this->localPrimaryKey)) {
                return $this->localPrimaryKey;
            } elseif (isset($this->localObject)) {
                return $this->localObject->primaryKey;
            }
        }
        if (isset($this->primaryObject)) {
            return $this->primaryObject->primaryKey;
        }

        return;
    }

    /**
     * [[@doctodo method_description:handle]].
     *
     * @param boolean $fromRelative   [[@doctodo param_description:fromRelative]] [optional]
     * @param array   $baseAttributes [[@doctodo param_description:baseAttributes]] [optional]
     *
     * @return [[@doctodo return_type:handle]] [[@doctodo return_description:handle]]
     */
    public function handle($fromRelative = false, $baseAttributes = [])
    {
        $this->baseAttributes = $baseAttributes;
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
        if ($fromRelative || !$this->dataSource->childOnly) {
            if (isset($baseAttributes['relationModels'][0]['parent_object_id'])) {
                $baseAttributes['indirectObject'] = $baseAttributes['relationModels'][0]['parent_object_id'];
            }
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

    /**
     * Get ignore foreign object.
     *
     * @return [[@doctodo return_type:getIgnoreForeignObject]] [[@doctodo return_description:getIgnoreForeignObject]]
     */
    public function getIgnoreForeignObject()
    {
        return $this->testIgnore($this->foreignObject, $this->dataSource->ignoreForeign);
    }

    /**
     * Get ignore local object.
     *
     * @return [[@doctodo return_type:getIgnoreLocalObject]] [[@doctodo return_description:getIgnoreLocalObject]]
     */
    public function getIgnoreLocalObject()
    {
        return $this->testIgnore($this->localObject, $this->dataSource->ignoreLocal);
    }

    /**
     * [[@doctodo method_description:testIgnore]].
     *
     * @param [[@doctodo param_type:object]] $object [[@doctodo param_description:object]]
     * @param [[@doctodo param_type:ignore]] $ignore [[@doctodo param_description:ignore]]
     *
     * @return [[@doctodo return_type:testIgnore]] [[@doctodo return_description:testIgnore]]
     */
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

    /**
     * [[@doctodo method_description:handleLocal]].
     *
     * @param array $baseAttributes [[@doctodo param_description:baseAttributes]] [optional]
     *
     * @return [[@doctodo return_type:handleLocal]] [[@doctodo return_description:handleLocal]]
     */
    protected function handleLocal($baseAttributes = [])
    {
        return false;
    }

    /**
     * [[@doctodo method_description:handleForeign]].
     *
     * @param array $baseAttributes [[@doctodo param_description:baseAttributes]] [optional]
     *
     * @return [[@doctodo return_type:handleForeign]] [[@doctodo return_description:handleForeign]]
     */
    protected function handleForeign($baseAttributes = [])
    {
        return false;
    }

    /**
     * Get handler.
     *
     * @return [[@doctodo return_type:getHandler]] [[@doctodo return_description:getHandler]]
     */
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

    /**
     * Get handling comparison.
     *
     * @return [[@doctodo return_type:getHandlingComparison]] [[@doctodo return_description:getHandlingComparison]]
     */
    public function getHandlingComparison()
    {
        return false;
    }

    /**
     * Get handling object.
     *
     * @param [[@doctodo param_type:a]] $a [[@doctodo param_description:a]]
     * @param [[@doctodo param_type:b]] $b [[@doctodo param_description:b]]
     *
     * @return [[@doctodo return_type:getHandlingObject]] [[@doctodo return_description:getHandlingObject]]
     */
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

    /**
     * Get primary object.
     *
     * @return [[@doctodo return_type:getPrimaryObject]] [[@doctodo return_description:getPrimaryObject]]
     */
    public function getPrimaryObject()
    {
        if ($this->isForeign) {
            return $this->foreignObject;
        } else {
            return $this->localObject;
        }
    }

    /**
     * Get companion object.
     *
     * @return [[@doctodo return_type:getCompanionObject]] [[@doctodo return_description:getCompanionObject]]
     */
    public function getCompanionObject()
    {
        if ($this->isForeign) {
            return $this->localObject;
        } else {
            return $this->foreignObject;
        }
    }

    /**
     * Set companion object.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @return [[@doctodo return_type:setCompanionObject]] [[@doctodo return_description:setCompanionObject]]
     */
    public function setCompanionObject($value)
    {
        if ($this->isForeign) {
            return $this->localObject = $value;
        } else {
            return $this->foreignObject = $value;
        }
    }

    /**
     * Get companion.
     *
     * @return [[@doctodo return_type:getCompanionId]] [[@doctodo return_description:getCompanionId]]
     */
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

        return;
    }

    /**
     * Set paired data item.
     *
     * @param cascade\components\dataInterface\DataItem $value [[@doctodo param_description:value]]
     */
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

    /**
     * Get paired data item.
     *
     * @return [[@doctodo return_type:getPairedDataItem]] [[@doctodo return_description:getPairedDataItem]]
     */
    public function getPairedDataItem()
    {
        return $this->_pairedDataItem;
    }

    /**
     * Set handled data item.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @return [[@doctodo return_type:setHandledDataItem]] [[@doctodo return_description:setHandledDataItem]]
     */
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

    /**
     * Get handled data item.
     *
     * @return [[@doctodo return_type:getHandledDataItem]] [[@doctodo return_description:getHandledDataItem]]
     */
    public function getHandledDataItem()
    {
        return $this->_handledDataItem;
    }

    /**
     * Get foreign object.
     *
     * @return [[@doctodo return_type:getForeignObject]] [[@doctodo return_description:getForeignObject]]
     */
    public function getForeignObject()
    {
        if (is_null($this->_foreignObject)) {
            $this->trigger(self::EVENT_LOAD_FOREIGN_OBJECT);
        }

        return $this->_foreignObject;
    }

    /**
     * Set foreign object.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setForeignObject($value)
    {
        $this->_foreignObject = $value;
    }

    /**
     * Get local object.
     *
     * @return [[@doctodo return_type:getLocalObject]] [[@doctodo return_description:getLocalObject]]
     */
    public function getLocalObject()
    {
        if (is_null($this->_localObject)) {
            $this->trigger(self::EVENT_LOAD_LOCAL_OBJECT);
        }

        return $this->_localObject;
    }

    /**
     * Set local object.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setLocalObject($value)
    {
        $this->_localObject = $value;
    }

    /**
     * Get action.
     *
     * @return [[@doctodo return_type:getAction]] [[@doctodo return_description:getAction]]
     */
    public function getAction()
    {
        return $this->dataSource->action;
    }

    /**
     * Get module.
     *
     * @return [[@doctodo return_type:getModule]] [[@doctodo return_description:getModule]]
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
