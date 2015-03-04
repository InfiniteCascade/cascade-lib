<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\helpers;

/**
 * StringHelper [@doctodo write class description for StringHelper].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Html extends \infinite\helpers\Html
{
    public static function prepareEditInPlace(&$htmlOptions, $model, $attribute, $relative = null)
    {
        $eip = ['data' => []];
        if (empty($model->primaryKey) || (isset($relative) && empty($relative->primaryKey))) {
            return false;
        }
        $eip['data']['object'] = $model->primaryKey;
        $eip['data']['attribute'] = $attribute;

        $eip['lastValue'] = $model->{$attribute};
        if (isset($relative)) {
            $eip['data']['relatedObject'] = $relative['primaryKey'];
        }
        $htmlOptions['data-edit-in-place'] = json_encode($eip);

        return true;
    }
}
