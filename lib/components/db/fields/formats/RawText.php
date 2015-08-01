<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
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
