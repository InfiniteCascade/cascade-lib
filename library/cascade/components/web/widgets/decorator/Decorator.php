<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;

/**
 * Decorator [@doctodo write class description for Decorator]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Decorator extends \yii\base\Behavior implements DecoratorInterface
{
    /**
     * __method_generateStart_description__
     * @return __return_generateStart_type__ __return_generateStart_description__
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
     * __method_generateEnd_description__
     * @return __return_generateEnd_type__ __return_generateEnd_description__
     */
    public function generateEnd()
    {
        $parts = [];
        $parts[] = Html::endTag('div'); // panel

        return implode("", $parts);
    }

    /**
     * Get widget classes
     * @return __return_getWidgetClasses_type__ __return_getWidgetClasses_description__
     */
    public function getWidgetClasses()
    {
        return [];
    }
}
