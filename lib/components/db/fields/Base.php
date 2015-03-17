<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\db\fields;

use cascade\components\db\fields\formats\Base as BaseFormat;
use cascade\components\db\fields\formats\Binary as BinaryType;
use cascade\components\db\fields\formats\Date as DateType;
use cascade\components\db\fields\formats\Text as TextType;
use canis\base\exceptions\Exception;
use Yii;

/**
 * Base [[@doctodo class_description:cascade\components\db\fields\Base]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Base extends \canis\base\Object
{
    /**
     * @var [[@doctodo var_type:formFieldClass]] [[@doctodo var_description:formFieldClass]]
     */
    public $formFieldClass;
    /**
     * @var [[@doctodo var_type:default]] [[@doctodo var_description:default]]
     */
    public $default;
    /**
     * @var [[@doctodo var_type:required]] [[@doctodo var_description:required]]
     */
    public $required = false;
    /**
     * @var [[@doctodo var_type:fieldSchema]] [[@doctodo var_description:fieldSchema]]
     */
    public $fieldSchema;
    /**
     * @var [[@doctodo var_type:priority]] [[@doctodo var_description:priority]]
     */
    public $priority;

    /**
     * @var [[@doctodo var_type:url]] [[@doctodo var_description:url]]
     */
    public $url; // wrap formatted text in link
    /**
     * @var [[@doctodo var_type:linkOptions]] [[@doctodo var_description:linkOptions]]
     */
    public $linkOptions = [];

    /**
     * @var [[@doctodo var_type:possiblePrimaryKeys]] [[@doctodo var_description:possiblePrimaryKeys]]
     */
    public $possiblePrimaryKeys = ['id'];

    /**
     * @var [[@doctodo var_type:_human]] [[@doctodo var_description:_human]]
     */
    protected $_human;
    /**
     * @var [[@doctodo var_type:_format]] [[@doctodo var_description:_format]]
     */
    protected $_format;
    /**
     * @var [[@doctodo var_type:_label]] [[@doctodo var_description:_label]]
     */
    protected $_label;
    /**
     * @var [[@doctodo var_type:_model]] [[@doctodo var_description:_model]]
     */
    protected $_model;
    /**
     * @var [[@doctodo var_type:_attributes]] [[@doctodo var_description:_attributes]]
     */
    protected $_attributes = false;
    /**
     * @var [[@doctodo var_type:_formField]] [[@doctodo var_description:_formField]]
     */
    protected $_formField;
    /**
     * @var [[@doctodo var_type:_multiline]] [[@doctodo var_description:_multiline]]
     */
    protected $_multiline;
    /**
     * @var [[@doctodo var_type:_locations]] [[@doctodo var_description:_locations]]
     */
    protected $_locations;

    const LOCATION_HIDDEN = 0x00;
    const LOCATION_DETAILS = 0x01;
    const LOCATION_HEADER = 0x02;
    const LOCATION_SUBHEADER = 0x03;

    /**
     * [[@doctodo method_description:__clone]].
     */
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
     * [[@doctodo method_description:determineFormatClass]].
     *
     * @return [[@doctodo return_type:determineFormatClass]] [[@doctodo return_description:determineFormatClass]]
     */
    public function determineFormatClass()
    {
        if (isset($this->fieldSchema)) {
            switch ($this->fieldSchema->type) {
                case 'date':
                    return DateType::className();
                break;
            }
            switch ($this->fieldSchema->dbType) {
                case 'tinyint(1)':
                    return BinaryType::className();
                break;
            }
        }

        return TextType::className();
    }

    /**
     * Get field.
     *
     * @return [[@doctodo return_type:getField]] [[@doctodo return_description:getField]]
     */
    public function getField()
    {
        if (isset($this->fieldSchema)) {
            return $this->fieldSchema->name;
        }

        return;
    }

    /**
     * [[@doctodo method_description:hasFile]].
     *
     * @return [[@doctodo return_type:hasFile]] [[@doctodo return_description:hasFile]]
     */
    public function hasFile()
    {
        return false;
    }

    /**
     * Set locations.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setLocations($value)
    {
        $this->_locations = $value;
    }

    /**
     * Get locations.
     *
     * @return [[@doctodo return_type:getLocations]] [[@doctodo return_description:getLocations]]
     */
    public function getLocations()
    {
        if (is_null($this->_locations)) {
            $this->_locations = $this->determineLocations();
        }

        return $this->_locations;
    }

    /**
     * [[@doctodo method_description:determineLocations]].
     *
     * @return [[@doctodo return_type:determineLocations]] [[@doctodo return_description:determineLocations]]
     */
    public function determineLocations()
    {
        if (!$this->human) {
            return [self::LOCATION_HIDDEN];
        }

        return [self::LOCATION_DETAILS];
    }

    /**
     * Set form field.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:setFormField]] [[@doctodo return_description:setFormField]]
     *
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
     * Set human.
     *
     * @param unknown $value
     *
     * @return unknown
     */
    public function setHuman($value)
    {
        $this->_human = $value;

        return true;
    }

    /**
     * Get human.
     *
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
     * Get multiline.
     *
     * @return [[@doctodo return_type:getMultiline]] [[@doctodo return_description:getMultiline]]
     */
    public function getMultiline()
    {
        if (is_null($this->_multiline)) {
            $this->_multiline = MultilineDetector::test($this->fieldSchema);
        }

        return $this->_multiline;
    }

    /**
     * Set multiline.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setMultiline($value)
    {
        $this->_multiline = $value;
    }

    /**
     * Get form field.
     *
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
     * Get model.
     *
     * @return unknown
     */
    public function getModel()
    {
        if (is_null($this->_model)) {
            return false;
        }

        return $this->_model;
    }

    /**
     * [[@doctodo method_description:resetModel]].
     *
     * @return [[@doctodo return_type:resetModel]] [[@doctodo return_description:resetModel]]
     */
    public function resetModel()
    {
        $this->_model = null;

        return $this->_model;
    }

    /**
     * [[@doctodo method_description:hasModel]].
     *
     * @return [[@doctodo return_type:hasModel]] [[@doctodo return_description:hasModel]]
     */
    public function hasModel()
    {
        return isset($this->_model);
    }
    /**
     * Set model.
     *
     * @param unknown $value
     *
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

    /**
     * Set attributes.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setAttributes($value)
    {
        $this->_attributes = $value;
        if ($this->model) {
            $this->_model->attributes = $value;
        }
    }

    /**
     * Get attributes.
     *
     * @return [[@doctodo return_type:getAttributes]] [[@doctodo return_description:getAttributes]]
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Get format.
     *
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
     * Set format.
     *
     * @param unknown $value
     *
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
     * Get formatted value.
     *
     * @return [[@doctodo return_type:getFormattedValue]] [[@doctodo return_description:getFormattedValue]]
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
     * Get form value.
     *
     * @return [[@doctodo return_type:getFormValue]] [[@doctodo return_description:getFormValue]]
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
     * Get value package.
     *
     * @return [[@doctodo return_type:getValuePackage]] [[@doctodo return_description:getValuePackage]]
     */
    public function getValuePackage()
    {
        return ['plain' => $this->value, 'rich' => $this->formattedValue];
    }

    /**
     * Get value.
     *
     * @return [[@doctodo return_type:getValue]] [[@doctodo return_description:getValue]]
     */
    public function getValue()
    {
        if (!isset($this->model->{$this->field})) {
            return;
        }

        return $this->model->{$this->field};
    }

    /**
     * Get label.
     *
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
     * Set label.
     *
     * @param unknown $value
     *
     * @return unknown
     */
    public function setLabel($value)
    {
        $this->_label = $value;

        return true;
    }

    /**
     * Get filter settings.
     *
     * @return [[@doctodo return_type:getFilterSettings]] [[@doctodo return_description:getFilterSettings]]
     */
    public function getFilterSettings()
    {
        if (!$this->human) {
            return false;
        }
        $settings = [];
        $settings['id'] = null;
        $settings['label'] = $this->label;
        $settings = array_merge($settings, $this->formField->filterSettings);

        return $settings;
    }
}
