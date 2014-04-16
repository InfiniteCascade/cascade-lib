<?php
/**
 * ./app/components/objects/fields/FormatText.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields\formats;

class Text extends Base
{
    public function get()
    {
        $result = $this->field->value;
        if (empty($result)) {
            $result = '<span class="empty">(none)</span>';
        }

        return $result;
    }
}
