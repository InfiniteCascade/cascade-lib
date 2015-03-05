<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields\formats;

use Yii;

/**
 * Date [[@doctodo class_description:cascade\components\db\fields\formats\Date]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Date extends Base
{
    /**
     * @inheritdoc
     */
    public function get()
    {
        $result = $this->field->value;
        if (empty($result)) {
            $result = '<span class="empty">(none)</span>';
        } else {
            $result = Yii::$app->formatter->asDate($result);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getFormValue()
    {
        $result = $this->field->value;
        if (empty($result)) {
            $result = null;
        } else {
            $dateFormat = strtotime($result);

            return Yii::$app->formatter->asDate($result);
        }

        return $result;
    }
}
