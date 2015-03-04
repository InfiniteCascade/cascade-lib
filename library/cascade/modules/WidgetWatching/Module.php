<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\WidgetWatching;

/**
 * Module [@doctodo write class description for Module].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\web\widgets\Module
{
    /**
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
