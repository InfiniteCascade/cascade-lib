<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
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
