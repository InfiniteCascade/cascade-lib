<?php
/**
 * ./app/components/web/form/RFormField.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web\form\fields;

use infinite\helpers\Html;

use cascade\components\web\form\FormObjectTrait;
use infinite\web\grid\CellContentTrait;

abstract class Base extends \infinite\base\Object implements \infinite\web\grid\CellContentInterface
{
    use FormObjectTrait;
    use CellContentTrait;

    public $modelField;
    public $options;
    public $smartOptions = [];
    public $htmlOptions = [];
    public $default;
    public $label;
    public $required; // for selectors
    public $showLabel = true;
    public $showError = true;

    protected $_type;
    protected $_model;

    abstract public function generate();

    /**
     *
     *
     * @param  unknown $formSettings (optional)
     * @return unknown
     */
    public function getModelFieldName($formSettings = [])
    {
        return "{$this->model->tabularPrefix}{$this->field}";
    }

    public function neightborFieldId($field)
    {
        $modelFields = $this->model->fields;
        if (!isset($modelFields[$field])) {
            return false;
        }

        return $modelFields[$field]->formField->fieldId;
    }

    public function getFieldId()
    {
        return Html::getInputId($this->model, $this->getModelFieldName());
    }

    /**
     *
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
     *
     *
     * @param  unknown $value
     * @return unknown
     */
    public function setType($value)
    {
        $this->_type = $value;

        return true;
    }

    /**
     *
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
     *
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

}
