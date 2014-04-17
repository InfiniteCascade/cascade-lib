<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form\fields;

use infinite\helpers\Html;

use cascade\components\web\form\FormObjectTrait;
use infinite\web\grid\CellContentTrait;

/**
 * Base [@doctodo write class description for Base]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Base extends \infinite\base\Object implements \infinite\web\grid\CellContentInterface
{
    use FormObjectTrait;
    use CellContentTrait;

    /**
     * @var __var_modelField_type__ __var_modelField_description__
     */
    public $modelField;
    /**
     * @var __var_options_type__ __var_options_description__
     */
    public $options;
    /**
     * @var __var_smartOptions_type__ __var_smartOptions_description__
     */
    public $smartOptions = [];
    /**
     * @var __var_htmlOptions_type__ __var_htmlOptions_description__
     */
    public $htmlOptions = [];
    /**
     * @var __var_default_type__ __var_default_description__
     */
    public $default;
    /**
     * @var __var_label_type__ __var_label_description__
     */
    public $label;
    /**
     * @var __var_required_type__ __var_required_description__
     */
    public $required; // for selectors
    /**
     * @var __var_showLabel_type__ __var_showLabel_description__
     */
    public $showLabel = true;
    /**
     * @var __var_showError_type__ __var_showError_description__
     */
    public $showError = true;

    /**
     * @var __var__type_type__ __var__type_description__
     */
    protected $_type;
    /**
     * @var __var__model_type__ __var__model_description__
     */
    protected $_model;

    /**
     * __method_generate_description__
     */
    abstract public function generate();

    /**
     * __method_getModelFieldName_description__
     * @param unknown $formSettings (optional)
     * @return unknown
     */
    public function getModelFieldName($formSettings = [])
    {
        return "{$this->model->tabularPrefix}{$this->field}";
    }

    /**
     * __method_neightborFieldId_description__
     * @param __param_field_type__ $field __param_field_description__
     * @return __return_neightborFieldId_type__ __return_neightborFieldId_description__
     */
    public function neightborFieldId($field)
    {
        $modelFields = $this->model->fields;
        if (!isset($modelFields[$field])) {
            return false;
        }

        return $modelFields[$field]->formField->fieldId;
    }

    /**
     * __method_getFieldId_description__
     * @return __return_getFieldId_type__ __return_getFieldId_description__
     */
    public function getFieldId()
    {
        return Html::getInputId($this->model, $this->getModelFieldName());
    }

    /**
     * __method_getType_description__
     * @return unknown
     */
    public function getType()
    {
        if (is_null($this->_type)) {
            $this->_type = FieldTypeDetector::detect($this->modelField);

        }

        return $this->_type;
    }

    /**
     * __method_setType_description__
     * @param unknown $value
     * @return unknown
     */
    public function setType($value)
    {
        $this->_type = $value;

        return true;
    }

    /**
     * __method_getModel_description__
     * @return unknown
     */
    public function getModel()
    {
        if (is_null($this->modelField)) {
            return false;
        }

        return $this->modelField->model;
    }

    /**
     * __method_getField_description__
     * @return unknown
     */
    public function getField()
    {
        if (is_null($this->modelField)) {
            return false;
        }

        return $this->modelField->field;
    }

}
