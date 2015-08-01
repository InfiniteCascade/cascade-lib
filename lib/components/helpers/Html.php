<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\helpers;

/**
 * Html [[@doctodo class_description:cascade\components\helpers\Html]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Html extends \canis\helpers\Html
{
    /**
     * [[@doctodo method_description:prepareEditInPlace]].
     *
     * @param [[@doctodo param_type:htmlOptions]] $htmlOptions [[@doctodo param_description:htmlOptions]]
     * @param [[@doctodo param_type:model]]       $model       [[@doctodo param_description:model]]
     * @param [[@doctodo param_type:attribute]]   $attribute   [[@doctodo param_description:attribute]]
     * @param [[@doctodo param_type:relative]]    $relative    [[@doctodo param_description:relative]] [optional]
     *
     * @return [[@doctodo return_type:prepareEditInPlace]] [[@doctodo return_description:prepareEditInPlace]]
     */
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
