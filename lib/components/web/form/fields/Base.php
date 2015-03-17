<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\form\fields;

use cascade\components\web\form\FormObjectTrait;
use canis\helpers\Html;
use canis\web\grid\CellContentTrait;

/**
 * Base [[@doctodo class_description:cascade\components\web\form\fields\Base]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Base extends \canis\base\Object implements \canis\web\grid\CellContentInterface
{
    use FormObjectTrait;
    use CellContentTrait;

    /**
     * @var [[@doctodo var_type:modelField]] [[@doctodo var_description:modelField]]
     */
    public $modelField;
    /**
     * @var [[@doctodo var_type:options]] [[@doctodo var_description:options]]
     */
    public $options;
    /**
     * @var [[@doctodo var_type:smartOptions]] [[@doctodo var_description:smartOptions]]
     */
    public $smartOptions = [];
    /**
     * @var [[@doctodo var_type:htmlOptions]] [[@doctodo var_description:htmlOptions]]
     */
    public $htmlOptions = [];
    /**
     * @var [[@doctodo var_type:default]] [[@doctodo var_description:default]]
     */
    public $default;
    /**
     * @var [[@doctodo var_type:label]] [[@doctodo var_description:label]]
     */
    public $label;
    /**
     * @var [[@doctodo var_type:required]] [[@doctodo var_description:required]]
     */
    public $required; // for selectors
    /**
     * @var [[@doctodo var_type:showLabel]] [[@doctodo var_description:showLabel]]
     */
    public $showLabel = true;
    /**
     * @var [[@doctodo var_type:showError]] [[@doctodo var_description:showError]]
     */
    public $showError = true;

    /**
     * @var [[@doctodo var_type:_type]] [[@doctodo var_description:_type]]
     */
    protected $_type;

    /**
     * [[@doctodo method_description:generate]].
     */
    abstract public function generate();

    /**
     * Get model field name.
     *
     * @param unknown $formSettings (optional)
     *
     * @return unknown
     */
    public function getModelFieldName($formSettings = [])
    {
        return "{$this->model->tabularPrefix}{$this->field}";
    }

    /**
     * [[@doctodo method_description:neightborFieldId]].
     *
     * @param [[@doctodo param_type:field]] $field [[@doctodo param_description:field]]
     *
     * @return [[@doctodo return_type:neightborFieldId]] [[@doctodo return_description:neightborFieldId]]
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
     * Get field.
     *
     * @return [[@doctodo return_type:getFieldId]] [[@doctodo return_description:getFieldId]]
     */
    public function getFieldId()
    {
        return Html::getInputId($this->model, $this->getModelFieldName());
    }

    /**
     * Get type.
     *
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
     * Set type.
     *
     * @param unknown $value
     *
     * @return unknown
     */
    public function setType($value)
    {
        $this->_type = $value;

        return true;
    }

    /**
     * Get model.
     *
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
     * Get field.
     *
     * @return unknown
     */
    public function getField()
    {
        if (is_null($this->modelField)) {
            return false;
        }

        return $this->modelField->field;
    }

    /**
     * Get filter settings.
     *
     * @return [[@doctodo return_type:getFilterSettings]] [[@doctodo return_description:getFilterSettings]]
     */
    public function getFilterSettings()
    {
        $s = [];
        $s['type'] = $this->filterType;
        $s['input'] = $this->filterInputType;
        $selectValues = $this->filterValues;
        if ($selectValues) {
            $s['values'] = $selectValues;
        }

        return $s;
    }

    /**
     * Get filter type.
     *
     * @return [[@doctodo return_type:getFilterType]] [[@doctodo return_description:getFilterType]]
     */
    public function getFilterType()
    {
        switch ($this->type) {
            case 'date':
            case 'time':
            case 'datetime':
                return $this->type;
            break;
            default:
                return 'string';
            break;
        }
    }

    /**
     * Get filter values.
     *
     * @return [[@doctodo return_type:getFilterValues]] [[@doctodo return_description:getFilterValues]]
     */
    public function getFilterValues()
    {
        if ($this->type === 'boolean') {
            return [0 => 'No', 1 => 'Yes'];
        }

        return false;
    }

    /**
     * Get filter input type.
     *
     * @return [[@doctodo return_type:getFilterInputType]] [[@doctodo return_description:getFilterInputType]]
     */
    public function getFilterInputType()
    {
        switch ($this->type) {
            case 'boolean':
                return 'select';
            break;
            default:
                return 'text';
            break;
        }
    }
}
