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
    public $location = 'right';
    public $parentWidget;
    public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\AreaDecorator';

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

    public function getCellContent()
    {
        return $this->generate();
    }

    public function getIsReady()
    {
        return true;
    }
}
