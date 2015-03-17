<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
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
