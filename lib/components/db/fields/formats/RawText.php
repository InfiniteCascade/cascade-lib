<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * RawText [[@doctodo class_description:cascade\components\db\fields\formats\RawText]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RawText extends Base
{
    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->field->value;
    }
}
