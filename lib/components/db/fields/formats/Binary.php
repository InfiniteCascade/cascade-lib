<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
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
