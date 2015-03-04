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
     * @var __var_dataSource_type__ __var_dataSource_description__
     */
    public $dataSource;

    /**
     * @var __var_localField_type__ __var_localField_description__
     */
    public $localField = false;
    /**
     * @var __var_foreignField_type__ __var_foreignField_description__
     */
    public $foreignField = false;
    /**
     * @var __var_foreignModel_type__ __var_foreignModel_description__
     */
    public $foreignModel = false;
    /**
     * @var __var_searchFields_type__ __var_searchFields_description__
     */
    public $searchFields;
    /**
     * @var __var_value_type__ __var_value_description__
     */
    public $value;
    /**
     * @var __var_filter_type__ __var_filter_description__
     */
    public $filter;
    /**
     * @var __var_taxonomy_type__ __var_taxonomy_description__
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
     * __method_extractValue_description__.
     *
     * @param __param_foreignModel_type__ $foreignModel __param_foreignModel_description__ [optional]
     *
     * @return __return_extractValue_type__ __return_extractValue_description__
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
