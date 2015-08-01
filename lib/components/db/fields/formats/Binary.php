<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * Binary [[@doctodo class_description:cascade\components\db\fields\formats\Binary]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Binary extends Base
{
    /**
     * @inheritdoc
     */
    public function get()
    {
        $result = $this->field->value;
        if (empty($result)) {
            $result = 'No';
        } else {
            $result = 'Yes';
        }

        return $result;
    }
}
