<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * FieldMap [@doctodo write class description for FieldMap].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class FieldMap extends \infinite\base\Object
{
    /**
     */
    public $dataSource;

    /**
     */
    public $localField = false;
    /**
     */
    public $foreignField = false;
    /**
     */
    public $foreignModel = false;
    /**
     */
    public $searchFields;
    /**
     */
    public $value;
    /**
     */
    public $filter;
    /**
     */
    public $taxonomy;

    public $mute = [];

    public $ignore = [];

    public function testIgnore($value)
    {
        if (is_array($value)) {
            $model = $value;
            foreach ($this->ignore as $field => $test) {
                $settings = [];
                if (is_numeric($field)) {
                    $settings = $test;
                    $field = isset($settings['field']);
                    $test = $settings['test'];
                }
                $value = null;
                if (isset($model[$field])) {
                    $value = $model[$field];
                }
                if (is_string($test)) {
                    $subvalue = null;
                    if (isset($model[$test])) {
                        $subvalue = $model[$test];
                    }
                    if ($value === $subvalue) {
                        return true;
                    }
                } elseif ($test instanceof Match && $test->test($value)) {
                    return true;
                }
            }
        } else {
            foreach ($this->ignore as $test) {
                if ($test instanceof Match && $test->test($value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     */
    public function extractValue($caller, $foreignModel = null, $localModel = null)
    {
        if (is_null($foreignModel)) {
            $foreignModel = $this->foreignModel;
        }
        $foreignField = $this->foreignField;

        $value = null;
        if (isset($this->value)) {
            if (is_callable($this->value)) {
                $value = call_user_func($this->value, $foreignModel, $this);
            } else {
                $value = $this->value;
            }
        } elseif (isset($foreignField)) {
            if (is_callable($foreignField)) {
                $value = call_user_func($foreignField, $foreignModel);
            } elseif (!is_object($foreignField) && is_array($foreignField)) {
                $value = $caller->buildLocalAttributes($foreignModel, $localModel, $caller->buildMap($foreignField));
            } elseif (is_string($foreignField)) {
                $value = (isset($foreignModel->{$foreignField}) ? $foreignModel->{$foreignField} : null);
            }
        }

        if (is_object($value) && $value instanceof DataItem) {
            $object = $value->handle();
            if ($object) {
                $value = $object->primaryKey;
            } else {
                $value = null;
            }
        }
        if (!is_array($value)) {
            if (isset($this->filter)) {
                $value = call_user_func($this->filter, $value);
            }

            $value = $this->dataSource->universalFilter($value);
        }

        return $value;
    }
}
