<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * FieldMap [@doctodo write class description for FieldMap]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
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

    /**
     * __method_extractValue_description__
     * @param __param_foreignModel_type__ $foreignModel __param_foreignModel_description__ [optional]
     * @return __return_extractValue_type__ __return_extractValue_description__
     */
    public function extractValue($foreignModel = null)
    {
        if (is_null($foreignModel)) {
            $foreignModel = $this->foreignModel;
        }

        $value = null;
        if (isset($this->value)) {
            if (is_callable($this->value)) {
                $value = call_user_func($this->value, $foreignModel, $this);
            } else {
                $value = $this->value;
            }
        } elseif (isset($this->foreignField)) {
            if (is_string($this->foreignField)) {
                $value = (isset($foreignModel->{$this->foreignField}) ? $foreignModel->{$this->foreignField} : null);
            } elseif (is_callable($this->foreignField)) {
                $value = call_user_func($this->foreignField, $foreignModel);
            }
        }
        if (isset($this->filter)) {
            $value = call_user_func($this->filter, $value);
        }

        return $value;
    }
}
