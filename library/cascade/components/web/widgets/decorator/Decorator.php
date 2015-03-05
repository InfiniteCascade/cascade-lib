<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;

/**
 * Decorator [[@doctodo class_description:cascade\components\web\widgets\decorator\Decorator]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Decorator extends \yii\base\Behavior implements DecoratorInterface
{
    /**
     * [[@doctodo method_description:generateStart]].
     *
     * @return [[@doctodo return_type:generateStart]] [[@doctodo return_description:generateStart]]
     */
    public function generateStart()
    {
        $parts = [];
        foreach ($this->owner->widgetClasses as $class) {
            Html::addCssClass($this->owner->htmlOptions, $class);
        }
        $parts[] = Html::beginTag('div', $this->owner->htmlOptions);

        return implode("", $parts);
    }

    /**
     * [[@doctodo method_description:generateEnd]].
     *
     * @return [[@doctodo return_type:generateEnd]] [[@doctodo return_description:generateEnd]]
     */
    public function generateEnd()
    {
        $parts = [];
        $parts[] = Html::endTag('div'); // panel

        return implode("", $parts);
    }

    /**
     * Get widget classes.
     *
     * @return [[@doctodo return_type:getWidgetClasses]] [[@doctodo return_description:getWidgetClasses]]
     */
    public function getWidgetClasses()
    {
        return [];
    }
}
