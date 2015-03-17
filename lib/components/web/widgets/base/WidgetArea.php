<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\base;

use cascade\components\web\widgets\BaseWidget;
use Yii;

/**
 * WidgetArea [[@doctodo class_description:cascade\components\web\widgets\base\WidgetArea]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class WidgetArea extends BaseWidget
{
    /**
     * @var [[@doctodo var_type:location]] [[@doctodo var_description:location]]
     */
    public $location = 'right';
    /**
     * @var [[@doctodo var_type:parentWidget]] [[@doctodo var_description:parentWidget]]
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
     *
     * @return [[@doctodo return_type:getCellContent]] [[@doctodo return_description:getCellContent]]
     */
    public function getCellContent()
    {
        return $this->generate();
    }

    /**
     * Get is ready.
     *
     * @return [[@doctodo return_type:getIsReady]] [[@doctodo return_description:getIsReady]]
     */
    public function getIsReady()
    {
        return true;
    }
}
