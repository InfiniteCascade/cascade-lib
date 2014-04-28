<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields\formats;

use Yii;

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
