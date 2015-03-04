<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form\fields;

use cascade\components\web\form\FormObjectTrait;
use infinite\helpers\Html;
use infinite\web\grid\CellContentTrait;

/**
 * Base [@doctodo write class description for Base].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Base extends \infinite\base\Object implements \infinite\web\grid\CellContentInterface
{
    use FormObjectTrait;
    use CellContentTrait;

    /**
     */
    public $modelField;
    /**
     */
    public $options;
    /**
     */
    public $smartOptions = [];
    /**
     */
    public $htmlOptions = [];
    /**
     */
    public $default;
    /**
     */
    public $label;
    /**
     */
    public $required; // for selectors
    /**
     */
    public $showLabel = true;
    /**
     */
    public $showError = true;

    /**
     */
    protected $_type;

    /**
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
     *
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

    public function getFilterValues()
    {
        if ($this->type === 'boolean') {
            return [0 => 'No', 1 => 'Yes'];
        }

        return false;
    }

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
