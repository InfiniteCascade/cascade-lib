<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

/**
 * Artificial [@doctodo write class description for Artificial].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Artificial extends Base
{
    /**
     * @inheritdoc
     */
    public $formFieldClass = false;
    /**
     * @var __var_fieldName_type__ __var_fieldName_description__
     */
    public $fieldName;
    /**
     * @var __var_fieldValue_type__ __var_fieldValue_description__
     */
    public $fieldValue;
    /**
     * @var __var_human_type__ __var_human_description__
     */
    public $human = true;
    /**
     * @var __var_multiline_type__ __var_multiline_description__
     */
    public $multiline = false;

    /**
     * @inheritdoc
     */
    public function getField()
    {
        return $this->fieldName;
    }

    /**
     * @inheritdoc
     */
    public function setFormField($value)
    {
        $this->_formField = false;
    }

    /**
     * @inheritdoc
     */
    public function getFormField()
    {
        return false;
    }

    public function determineFormatClass()
    {
        return 'cascade\components\db\fields\formats\RawText';
    }
}
