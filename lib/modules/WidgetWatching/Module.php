<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\modules\WidgetWatching;

/**
 * Module [[@doctodo class_description:cascade\modules\WidgetWatching\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\web\widgets\Module
{
    /**
     * @var [[@doctodo var_type:_title]] [[@doctodo var_description:_title]]
     */
    protected $_title = 'Watching';
    /**
     * @inheritdoc
     */
    public $icon = 'fa fa-history';
    /**
     * @inheritdoc
     */
    public $priority = -99999;

    /**
     * @inheritdoc
     */
    public $widgetNamespace = 'cascade\modules\WidgetWatching\widgets';
}
