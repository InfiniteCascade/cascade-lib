<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * Base [[@doctodo class_description:cascade\components\db\fields\formats\Base]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Base extends \teal\base\Object
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
