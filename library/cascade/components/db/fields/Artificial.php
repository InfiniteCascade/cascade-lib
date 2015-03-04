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
     */
    public $fieldName;
    /**
     */
    public $fieldValue;
    /**
     */
    public $human = true;
    /**
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
