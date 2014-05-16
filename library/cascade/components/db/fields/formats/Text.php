<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * Text [@doctodo write class description for Text]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Text extends Base
{
    public $showEmptyString = true;
    /**
    * @inheritdoc
     */
    public function get()
    {
        $result = $this->field->value;
        if (is_object($result)) {
            if (isset($result->descriptor)) {
                $result = $result->viewLink;
            } else {
                $result = null;
            }
        }
        $result = preg_replace("/\<br ?\\?\>/", "\n", $result);
        $result = preg_replace("/\n+/", "\n", $result);
        if (empty($result) && $this->showEmptyString) {
            $result = '<span class="empty">(none)</span>';
        } elseif (empty($result)) {
            $result = null;
        }

        return $result;
    }
}
