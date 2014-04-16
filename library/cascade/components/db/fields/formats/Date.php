<?php
/**
 * ./app/components/objects/fields/FormatText.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields\formats;

use Yii;

class Date extends Base
{
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
