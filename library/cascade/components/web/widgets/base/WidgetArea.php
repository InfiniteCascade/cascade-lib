<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use cascade\components\web\widgets\BaseWidget;
use Yii;

/**
 * WidgetArea [@doctodo write class description for WidgetArea].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class WidgetArea extends BaseWidget
{
    /**
     */
    public $location = 'right';
    /**
     */
    public $parentWidget;
    /**
     * @inheritdoc
     */
    public $defaultDecoratorClass = 'cascade\components\web\widgets\decorator\AreaDecorator';

    /**
     * @inheritdoc
     */
    public function generate()
    {
        Yii::beginProfile(get_called_class() . ':' . __FUNCTION__);
        $this->ensureDecorator();
        $content = $this->generateContent();
        if ($content === false) {
            return;
        }
        $result = $this->generateStart() . $this->generateHeader() . $content . $this->generateFooter() . $this->generateEnd();
        Yii::endProfile(get_called_class() . ':' . __FUNCTION__);

        return $result;
    }

    /**
     * Get cell content.
     */
    public function getCellContent()
    {
        return $this->generate();
    }

    /**
     * Get is ready.
     */
    public function getIsReady()
    {
        return true;
    }
}
