<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use cascade\components\web\widgets\Widget;

/**
 * BaseList [[@doctodo class_description:cascade\components\web\widgets\base\BaseList]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class BaseList extends Widget implements ObjectWidgetInterface, ListWidgetInterface
{
    use ListWidgetTrait, ObjectWidgetTrait {
        ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
        ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
        ObjectWidgetTrait::buildContext insteadof ListWidgetTrait;
        ListWidgetTrait::buildContext as buildContextBase;
    }
}
