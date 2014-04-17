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
**/
abstract class Base extends \infinite\base\Object
{
    public $formFieldClass;
    public $default;
    public $required = false;
    public $fieldSchema;
    public $priority;

    public $url; // wrap formatted text in link
    public $linkOptions = [];

    public $possiblePrimaryKeys = ['id'];

    protected $_human;
    protected $_format;
    protected $_label;
    protected $_model;
    protected $_formField;
    protected $_multiline;
    protected $_locations;

    const LOCATION_HIDDEN = 0x00;
    const LOCATION_DETAILS = 0x01;
    const LOCATION_HEADER = 0x02;
    const LOCATION_SUBHEADER = 0x03;

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

    public function determineFormatClass()
    {
        if (isset($this->fieldSchema)) {
            switch ($this->fieldSchema->type) {
                case 'date':
                    return 'cascade\\components\\db\\fields\\formats\\Date';
                break;
            }
        }

        return 'cascade\\components\\db\\fields\\formats\\Text';
    }

    public function getField()
    {
        if (isset($this->fieldSchema)) {
            return $this->fieldSchema->name;
        }

        return null;
    }

    public function hasFile()
    {
        return false;
    }

    public function setLocations($value)
    {
        $this->_locations = $value;
    }

    public function getLocations()
    {
        if (is_null($this->_locations)) {
            $this->_locations = $this->determineLocations();
        }

        return $this->_locations;
    }

    public function determineLocations()
    {
        if (!$this->human) {
            return [self::LOCATION_HIDDEN];
        }

        return [self::LOCATION_DETAILS];
    }

    public function setFormField($value)
    {
        if (is_array($value)) {
            if (is_null($this->formFieldClass)) {
                throw new Exception("DB Field incorrectly set up. What is the form class?");
            }
            $config = $value;
            $config['class'] = $this->formFieldClass;
            $config['modelField'] = $this;
            $value = Yii::createObject($config);
        }

        $this->_formField = $value;

        return true;
    }

    /**
     *
     *
     * @param  unknown $value
     * @return unknown
     */
    public function setHuman($value)
    {
        $this->_human = $value;

        return true;
    }

    /**
     *
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

    public function getMultiline()
    {
        if (is_null($this->_multiline)) {
            $this->_multiline = MultilineDetector::test($this->fieldSchema);
        }

        return $this->_multiline;
    }

    public function setMultiline($value)
    {
        $this->_multiline = $value;
    }

    /**
     *
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
     *
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
     *
     *
     * @param  unknown $value
     * @return unknown
     */
    public function setModel($value)
    {
        $this->_model = $value;

        return true;
    }

    /**
     *
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
     *
     *
     * @param  unknown $value
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

    public function getFormattedValue()
    {
        if ($this->format instanceof BaseFormat) {
            $formattedValue = $this->format->get();
        } elseif (is_callable($this->format) || (is_array($this->format) && !empty($this->format[0]) && is_object($this->format[0]))) {
            $formattedValue = $this->evaluateExpression($this->format, [$this->value]);
        } else {
            $formattedValue = $this->value;
        }

        return $formattedValue;
    }

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

    public function getValuePackage()
    {
        return ['plain' => $this->value, 'rich' => $this->formattedValue];
    }

    public function getValue()
    {
        if (!isset($this->model->{$this->field})) {
            return null;
        }

        return $this->model->{$this->field};
    }

    /**
     *
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
     *
     *
     * @param  unknown $value
     * @return unknown
     */
    public function setLabel($value)
    {
        $this->_label = $value;

        return true;
    }
}
