<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
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
