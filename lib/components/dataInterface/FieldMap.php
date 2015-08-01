<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface;

/**
 * FieldMap [[@doctodo class_description:cascade\components\dataInterface\FieldMap]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class FieldMap extends \canis\base\Object
{
    /**
     * @var [[@doctodo var_type:dataSource]] [[@doctodo var_description:dataSource]]
     */
    public $dataSource;

    /**
     * @var [[@doctodo var_type:localField]] [[@doctodo var_description:localField]]
     */
    public $localField = false;
    /**
     * @var [[@doctodo var_type:foreignField]] [[@doctodo var_description:foreignField]]
     */
    public $foreignField = false;
    /**
     * @var [[@doctodo var_type:foreignModel]] [[@doctodo var_description:foreignModel]]
     */
    public $foreignModel = false;
    /**
     * @var [[@doctodo var_type:searchFields]] [[@doctodo var_description:searchFields]]
     */
    public $searchFields;
    /**
     * @var [[@doctodo var_type:value]] [[@doctodo var_description:value]]
     */
    public $value;
    /**
     * @var [[@doctodo var_type:filter]] [[@doctodo var_description:filter]]
     */
    public $filter;
    /**
     * @var [[@doctodo var_type:taxonomy]] [[@doctodo var_description:taxonomy]]
     */
    public $taxonomy;

    /**
     * @var [[@doctodo var_type:mute]] [[@doctodo var_description:mute]]
     */
    public $mute = [];

    /**
     * @var [[@doctodo var_type:ignore]] [[@doctodo var_description:ignore]]
     */
    public $ignore = [];

    /**
     * [[@doctodo method_description:testIgnore]].
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @return [[@doctodo return_type:testIgnore]] [[@doctodo return_description:testIgnore]]
     */
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
     * [[@doctodo method_description:extractValue]].
     *
     * @param [[@doctodo param_type:caller]]       $caller       [[@doctodo param_description:caller]]
     * @param [[@doctodo param_type:foreignModel]] $foreignModel [[@doctodo param_description:foreignModel]] [optional]
     * @param [[@doctodo param_type:localModel]]   $localModel   [[@doctodo param_description:localModel]] [optional]
     *
     * @return [[@doctodo return_type:extractValue]] [[@doctodo return_description:extractValue]]
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
