<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * RawText [@doctodo write class description for RawText]
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
