<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

class RawText extends BaseFormat
{
    public function get()
    {
        return $this->_field->value;
    }
}
