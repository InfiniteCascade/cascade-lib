<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\fields\formats;

/**
 * Text [[@doctodo class_description:cascade\components\db\fields\formats\Text]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Text extends Base
{
    /**
     * @var [[@doctodo var_type:showEmptyString]] [[@doctodo var_description:showEmptyString]]
     */
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
