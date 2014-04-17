<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;

/**
 * AreaDecorator [@doctodo write class description for AreaDecorator]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class AreaDecorator extends Decorator
{
    /**
     * @var __var_panelCssClass_type__ __var_panelCssClass_description__
     */
    public $panelCssClass = 'panel';
    /**
     * @var __var_panelStateCssClass_type__ __var_panelStateCssClass_description__
     */
    public $panelStateCssClass = 'panel-default';
    /**
     * @var __var_gridCellClass_type__ __var_gridCellClass_description__
     */
    public $gridCellClass = 'infinite\web\grid\Cell';

    /**
    * @inheritdoc
    **/
    public function generateStart()
    {
        Html::addCssClass($this->owner->htmlOptions, $this->owner->panelCssClass);
        Html::addCssClass($this->owner->htmlOptions, $this->owner->panelStateCssClass);

        return parent::generateStart();
    }

    /**
     * __method_generateHeader_description__
     * @return __return_generateHeader_type__ __return_generateHeader_description__
     */
    public function generateHeader()
    {
        $parts = [];
        $parts[] = Html::beginTag('div', ['class' => 'panel-body']);

        return implode("", $parts);
    }

    /**
     * __method_generateFooter_description__
     * @return __return_generateFooter_type__ __return_generateFooter_description__
     */
    public function generateFooter()
    {
        $parts = [];
        $parts[] = Html::endTag('div'); // panel-body

        return implode("", $parts);
    }

    /**
     * Get panel title
     * @return __return_getPanelTitle_type__ __return_getPanelTitle_description__
     */
    public function getPanelTitle()
    {
        return $this->owner->parseText($this->owner->title);
    }
}
