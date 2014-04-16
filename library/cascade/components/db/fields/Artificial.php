<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

class Artificial extends Base
{
    public $formFieldClass = false;
    public $fieldName;
    public $fieldValue;
    public $human = true;
    public $multiline = false;

    public function getField()
    {
        return $this->fieldName;
    }

    public function setFormField($value)
    {
        $this->_formField = false;
    }

    public function getFormField()
    {
        return false;
    }
}
