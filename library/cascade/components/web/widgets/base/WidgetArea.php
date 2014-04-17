<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

use cascade\components\web\widgets\BaseWidget;

/**
 * WidgetArea [@doctodo write class description for WidgetArea]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class WidgetArea extends BaseWidget
{
    /**
     * @var __var_location_type__ __var_location_description__
     */
    public $location = 'right';
    /**
     * @var __var_parentWidget_type__ __var_parentWidget_description__
     */
    public $parentWidget;
    /**
     * @inheritdoc
     */
    public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\AreaDecorator';

    /**
    * @inheritdoc
    **/
    public function generate()
    {
        Yii::beginProfile(get_called_class() .':'. __FUNCTION__);
        $this->ensureDecorator();
        $content = $this->generateContent();
        if ($content === false) { return; }
        $result = $this->generateStart() . $this->generateHeader() . $content . $this->generateFooter() . $this->generateEnd();
        Yii::endProfile(get_called_class() .':'. __FUNCTION__);

        return $result;
    }

    /**
     * __method_getCellContent_description__
     * @return __return_getCellContent_type__ __return_getCellContent_description__
     */
    public function getCellContent()
    {
        return $this->generate();
    }

    /**
     * __method_getIsReady_description__
     * @return __return_getIsReady_type__ __return_getIsReady_description__
     */
    public function getIsReady()
    {
        return true;
    }
}
