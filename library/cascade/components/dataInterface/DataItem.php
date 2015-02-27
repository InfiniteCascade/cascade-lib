<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use Yii;
use cascade\models\DataInterface;

/**
 * DataItem [@doctodo write class description for DataItem]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataItem extends \infinite\base\Component
{
    /**
     * @var __var_dataSource_type__ __var_dataSource_description__
     */
    public $dataSource;
    /**
     * @var __var_isForeign_type__ __var_isForeign_description__
     */
    public $isForeign = true;

        public $localModelError = false;
        public $foreignModelError = false;

    /**
     * @var __var__pairedDataItem_type__ __var__pairedDataItem_description__
     */
    protected $_pairedDataItem;
    /**
     * @var __var__handledDataItem_type__ __var__handledDataItem_description__
     */
    protected $_handledDataItem = false;
    /**
     * @var __var__foreignObject_type__ __var__foreignObject_description__
     */
    protected $_foreignObject;
    /**
     * @var __var_foreignPrimaryKey_type__ __var_foreignPrimaryKey_description__
     */
    public $foreignPrimaryKey;

    /**
     * @var __var__localObject_type__ __var__localObject_description__
     */
    protected $_localObject;
    /**
     * @var __var_localPrimaryKey_type__ __var_localPrimaryKey_description__
     */
    public $localPrimaryKey;

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
     * __method_searchLocalObject_description__
     * @param __param_event_type__              $event __param_event_description__
     * @return __return_searchLocalObject_type__ __return_searchLocalObject_description__
     */
    protected function searchLocalObject($event)
    {
        if (isset($this->foreignObject) && !isset($this->_localObject) && isset($this->dataSource->search)) {
            if (($localObject = $this->dataSource->search->searchLocal($this)) && !empty($localObject)) {
                $this->localObject = $localObject;
            }

            // if (!$this->ignoreForeignObject) {
            //     \d([$localObject]);
            //     throw new \Exception("boom");
            //     exit;
            // }
            if ($localObject === false) {
                $this->localModelError = true;
            }
            // if (empty($this->localObject)) {
            //     \d($this->foreignObject->attributes);
            //     unset($this->dataSource->search->dataSource);
            //     \d($this->dataSource->search);
            //     \d("should have local object!");exit;
            // }
        }

        return true;
    }

    /**
     * __method_clean_description__
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
     * Get id
     * @return __return_getId_type__ __return_getId_description__
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

        return null;
    }

    /**
     * __method_handle_description__
     * @param boolean                $fromParent     __param_fromParent_description__ [optional]
     * @param array                  $baseAttributes __param_baseAttributes_description__ [optional]
     * @return __return_handle_type__ __return_handle_description__
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
     * Get ignore foreign object
     * @return __return_getIgnoreForeignObject_type__ __return_getIgnoreForeignObject_description__
     */
    public function getIgnoreForeignObject()
    {
        return $this->testIgnore($this->foreignObject, $this->dataSource->ignoreForeign);
    }

    /**
     * Get ignore local object
     * @return __return_getIgnoreLocalObject_type__ __return_getIgnoreLocalObject_description__
     */
    public function getIgnoreLocalObject()
    {
        return $this->testIgnore($this->localObject, $this->dataSource->ignoreLocal);
    }

    /**
     * __method_testIgnore_description__
     * @param __param_object_type__      $object __param_object_description__
     * @param __param_ignore_type__      $ignore __param_ignore_description__
     * @return __return_testIgnore_type__ __return_testIgnore_description__
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
     * __method_handleLocal_description__
     * @param array                       $baseAttributes __param_baseAttributes_description__ [optional]
     * @return __return_handleLocal_type__ __return_handleLocal_description__
     */
    protected function handleLocal($baseAttributes = [])
    {
        return false;
    }

    /**
     * __method_handleForeign_description__
     * @param array                         $baseAttributes __param_baseAttributes_description__ [optional]
     * @return __return_handleForeign_type__ __return_handleForeign_description__
     */
    protected function handleForeign($baseAttributes = [])
    {
        return false;
    }

    /**
     * Get handler
     * @return __return_getHandler_type__ __return_getHandler_description__
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
     * Get handling comparison
     * @return __return_getHandlingComparison_type__ __return_getHandlingComparison_description__
     */
    public function getHandlingComparison()
    {
        return false;
    }

    /**
     * Get handling object
     * @param __param_a_type__                  $a __param_a_description__
     * @param __param_b_type__                  $b __param_b_description__
     * @return __return_getHandlingObject_type__ __return_getHandlingObject_description__
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
     * Get primary object
     * @return __return_getPrimaryObject_type__ __return_getPrimaryObject_description__
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
     * Get companion object
     * @return __return_getCompanionObject_type__ __return_getCompanionObject_description__
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
     * Set companion object
     * @param __param_value_type__               $value __param_value_description__
     * @return __return_setCompanionObject_type__ __return_setCompanionObject_description__
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
     * Get companion
     * @return __return_getCompanionId_type__ __return_getCompanionId_description__
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

        return null;
    }

    /**
     * Set paired data item
     * @param cascade\components\dataInterface\DataItem $value __param_value_description__
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
     * Get paired data item
     * @return __return_getPairedDataItem_type__ __return_getPairedDataItem_description__
     */
    public function getPairedDataItem()
    {
        return $this->_pairedDataItem;
    }

    /**
     * Set handled data item
     * @param __param_value_type__               $value __param_value_description__
     * @return __return_setHandledDataItem_type__ __return_setHandledDataItem_description__
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
     * Get handled data item
     * @return __return_getHandledDataItem_type__ __return_getHandledDataItem_description__
     */
    public function getHandledDataItem()
    {
        return $this->_handledDataItem;
    }

    /**
     * Get foreign object
     * @return __return_getForeignObject_type__ __return_getForeignObject_description__
     */
    public function getForeignObject()
    {
        if (is_null($this->_foreignObject)) {
            $this->trigger(self::EVENT_LOAD_FOREIGN_OBJECT);
        }

        return $this->_foreignObject;
    }

    /**
     * Set foreign object
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setForeignObject($value)
    {
        $this->_foreignObject = $value;
    }

    /**
     * Get local object
     * @return __return_getLocalObject_type__ __return_getLocalObject_description__
     */
    public function getLocalObject()
    {
        if (is_null($this->_localObject)) {
            $this->trigger(self::EVENT_LOAD_LOCAL_OBJECT);
        }

        return $this->_localObject;
    }

    /**
     * Set local object
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setLocalObject($value)
    {
        $this->_localObject = $value;
    }

    /**
     * Get action
     * @return __return_getAction_type__ __return_getAction_description__
     */
    public function getAction()
    {
        return $this->dataSource->action;
    }

    /**
     * Get module
     * @return __return_getModule_type__ __return_getModule_description__
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
