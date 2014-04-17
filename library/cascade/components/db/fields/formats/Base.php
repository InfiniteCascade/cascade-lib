<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * Base [@doctodo write class description for Base]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Base extends \infinite\base\Object
{
    /**
     * @var __var_field_type__ __var_field_description__
     */
    public $field;
    /**
     * __method_get_description__
     */
    abstract public function get();
    /**
     * __method_getFormValue_description__
     * @return __return_getFormValue_type__ __return_getFormValue_description__
     */
    public function getFormValue()
    {
        return $this->field->value;
    }
}
