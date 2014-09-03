<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use Yii;
use infinite\base\exceptions\Exception;
use cascade\components\db\fields\formats\Base as BaseFormat;

/**
 * Base [@doctodo write class description for Base]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Base extends \infinite\base\Object
{
    /**
     * @var __var_formFieldClass_type__ __var_formFieldClass_description__
     */
    public $formFieldClass;
    /**
     * @var __var_default_type__ __var_default_description__
     */
    public $default;
    /**
     * @var __var_required_type__ __var_required_description__
     */
    public $required = false;
    /**
     * @var __var_fieldSchema_type__ __var_fieldSchema_description__
     */
    public $fieldSchema;
    /**
     * @var __var_priority_type__ __var_priority_description__
     */
    public $priority;

    /**
     * @var __var_url_type__ __var_url_description__
     */
    public $url; // wrap formatted text in link
    /**
     * @var __var_linkOptions_type__ __var_linkOptions_description__
     */
    public $linkOptions = [];

    /**
     * @var __var_possiblePrimaryKeys_type__ __var_possiblePrimaryKeys_description__
     */
    public $possiblePrimaryKeys = ['id'];

    /**
     * @var __var__human_type__ __var__human_description__
     */
    protected $_human;
    /**
     * @var __var__format_type__ __var__format_description__
     */
    protected $_format;
    /**
     * @var __var__label_type__ __var__label_description__
     */
    protected $_label;
    /**
     * @var __var__model_type__ __var__model_description__
     */
    protected $_model;
    protected $_attributes = false;
    /**
     * @var __var__formField_type__ __var__formField_description__
     */
    protected $_formField;
    /**
     * @var __var__multiline_type__ __var__multiline_description__
     */
    protected $_multiline;
    /**
     * @var __var__locations_type__ __var__locations_description__
     */
    protected $_locations;

    const LOCATION_HIDDEN = 0x00;
    const LOCATION_DETAILS = 0x01;
    const LOCATION_HEADER = 0x02;
    const LOCATION_SUBHEADER = 0x03;


    public function __clone()
    {
        $this->formField = clone $this->formField;
        $this->formField->modelField = $this;
        $this->format = clone $this->format;
        if (isset($this->_model)) {
            $this->_model = clone $this->_model;
        }
        $this->fieldSchema = clone $this->fieldSchema;
    }

    /**
    * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!is_null($this->default) && !$this->model->isAttributeChanged($this->field)) {
            $this->model->{$this->field} = $this->default;
        }
        if (in_array($this->field, $this->possiblePrimaryKeys)) {
            $this->required = true;
        }
    }


    /**
     * __method_determineFormatClass_description__
     * @return __return_determineFormatClass_type__ __return_determineFormatClass_description__
     */
    public function determineFormatClass()
    {
        if (isset($this->fieldSchema)) {
            switch ($this->fieldSchema->type) {
                case 'date':
                    return 'cascade\\components\\db\\fields\\formats\\Date';
                break;
            }
            switch ($this->fieldSchema->dbType) {
                case 'tinyint(1)':
                    return 'cascade\\components\\db\\fields\\formats\\Binary';
                break;
            }
        }

        return 'cascade\\components\\db\\fields\\formats\\Text';
    }

    /**
     * Get field
     * @return __return_getField_type__ __return_getField_description__
     */
    public function getField()
    {
        if (isset($this->fieldSchema)) {
            return $this->fieldSchema->name;
        }

        return null;
    }

    /**
     * __method_hasFile_description__
     * @return __return_hasFile_type__ __return_hasFile_description__
     */
    public function hasFile()
    {
        return false;
    }

    /**
     * Set locations
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setLocations($value)
    {
        $this->_locations = $value;
    }

    /**
     * Get locations
     * @return __return_getLocations_type__ __return_getLocations_description__
     */
    public function getLocations()
    {
        if (is_null($this->_locations)) {
            $this->_locations = $this->determineLocations();
        }

        return $this->_locations;
    }

    /**
     * __method_determineLocations_description__
     * @return __return_determineLocations_type__ __return_determineLocations_description__
     */
    public function determineLocations()
    {
        if (!$this->human) {
            return [self::LOCATION_HIDDEN];
        }

        return [self::LOCATION_DETAILS];
    }

    /**
     * Set form field
     * @param __param_value_type__         $value __param_value_description__
     * @return __return_setFormField_type__ __return_setFormField_description__
     * @throws Exception __exception_Exception_description__
     */
    public function setFormField($value)
    {
        if (is_array($value)) {
            if (is_null($this->formFieldClass)) {
                throw new Exception("DB Field incorrectly set up. What is the form class?");
            }
            if (is_null($this->_formField)) {
                $config = $value;
                $config['class'] = $this->formFieldClass;
                $config['modelField'] = $this;
                $value = Yii::createObject($config);
            } else {
                $settings = $value;
                $value = $this->_formField;
                unset($settings['class']);
                Yii::configure($value, $settings);
            }
        }

        $this->_formField = $value;

        return true;
    }

    /**
     * Set human
     * @param unknown $value
     * @return unknown
     */
    public function setHuman($value)
    {
        $this->_human = $value;

        return true;
    }

    /**
     * Get human
     * @return unknown
     */
    public function getHuman()
    {
        if (is_null($this->_human)) {
            $this->_human = HumanFieldDetector::test($this->fieldSchema);
        }

        return $this->_human;
    }

    /**
     * Get multiline
     * @return __return_getMultiline_type__ __return_getMultiline_description__
     */
    public function getMultiline()
    {
        if (is_null($this->_multiline)) {
            $this->_multiline = MultilineDetector::test($this->fieldSchema);
        }

        return $this->_multiline;
    }

    /**
     * Set multiline
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setMultiline($value)
    {
        $this->_multiline = $value;
    }

    /**
     * Get form field
     * @return unknown
     */
    public function getFormField()
    {
        if (is_null($this->_formField)) {
            $this->formField = [];
        }

        return $this->_formField;
    }

    /**
     * Get model
     * @return unknown
     */
    public function getModel()
    {
        if (is_null($this->_model)) {
            return false;
        }

        return $this->_model;
    }

    public function resetModel()
    {
        $this->_model = null;
        return $this->_model;
    }

    public function hasModel()
    {
        return isset($this->_model);
    }
    /**
     * Set model
     * @param unknown $value
     * @return unknown
     */
    public function setModel($value)
    {
        $this->_model = $value;
        if (is_object($value) && $this->_attributes) {
            $this->_model->attributes = $this->_attributes;
        }
        return true;
    }

    public function setAttributes($value)
    {
        $this->_attributes = $value;
        if ($this->model) {
            $this->_model->attributes = $value;
        }
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Get format
     * @return unknown
     */
    public function getFormat()
    {
        if (is_null($this->_format)) {
            $this->format = [];
        }

        return $this->_format;
    }

    /**
     * Set format
     * @param unknown $value
     * @return unknown
     */
    public function setFormat($value)
    {
        if (is_array($value)) {
            if (!isset($value['class'])) {
                $value['class'] = $this->determineFormatClass();
            }
            $value['field'] = $this;
            $value = Yii::createObject($value);
        }
        $this->_format = $value;
    }

    /**
     * Get formatted value
     * @return __return_getFormattedValue_type__ __return_getFormattedValue_description__
     */
    public function getFormattedValue()
    {
        if ($this->format instanceof BaseFormat) {
            $formattedValue = $this->format->get();
        } elseif (is_callable($this->format) || (is_array($this->format) && !empty($this->format[0]) && is_object($this->format[0]))) {
            $formattedValue = $this->evaluateExpression($this->format, [$this->value]);
        } else {
            $formattedValue = $this->value;
        }

        if (is_object($formattedValue)) {
            $formattedValue = $formattedValue->viewLink;
        }
        return $formattedValue;
    }

    /**
     * Get form value
     * @return __return_getFormValue_type__ __return_getFormValue_description__
     */
    public function getFormValue()
    {
        if ($this->format instanceof BaseFormat) {
            $formValue = $this->format->getFormValue();
        } elseif (is_callable($this->format) || (is_array($this->format) && !empty($this->format[0]) && is_object($this->format[0]))) {
            $formValue = $this->evaluateExpression($this->format, [$this->value]);
        } else {
            $formValue = $this->value;
        }

        return $formValue;
    }

    /**
     * Get value package
     * @return __return_getValuePackage_type__ __return_getValuePackage_description__
     */
    public function getValuePackage()
    {
        return ['plain' => $this->value, 'rich' => $this->formattedValue];
    }

    /**
     * Get value
     * @return __return_getValue_type__ __return_getValue_description__
     */
    public function getValue()
    {
        if (!isset($this->model->{$this->field})) {
            return null;
        }

        return $this->model->{$this->field};
    }

    /**
     * Get label
     * @return unknown
     */
    public function getLabel()
    {
        if (is_null($this->_label)) {
            $this->_label = $this->getModel()->getAttributeLabel($this->field);
        }

        return $this->_label;
    }

    /**
     * Set label
     * @param unknown $value
     * @return unknown
     */
    public function setLabel($value)
    {
        $this->_label = $value;
        return true;
    }

    public function getFilterSettings()
    {
        if (!$this->human) { return false; }
        $settings = [];
        $settings['id'] = null;
        $settings['label'] = $this->label;
        $settings = array_merge($settings, $this->formField->filterSettings);
        return $settings;
    }
}
