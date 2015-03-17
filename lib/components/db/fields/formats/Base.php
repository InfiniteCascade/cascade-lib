<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * Base [[@doctodo class_description:cascade\components\db\fields\formats\Base]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Base extends \canis\base\Object
{
    /**
     * @var [[@doctodo var_type:field]] [[@doctodo var_description:field]]
     */
    public $field;
    /**
     * Get.
     */
    abstract public function get();
    /**
     * Get form value.
     *
     * @return [[@doctodo return_type:getFormValue]] [[@doctodo return_description:getFormValue]]
     */
    public function getFormValue()
    {
        return $this->field->value;
    }
}
