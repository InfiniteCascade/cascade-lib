<?php
/**
 * ./app/components/objects/fields/BaseFormat.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields\formats;

abstract class Base extends \infinite\base\Object
{
    public $field;
    abstract public function get();
    public function getFormValue()
    {
        return $this->field->value;
    }
}
