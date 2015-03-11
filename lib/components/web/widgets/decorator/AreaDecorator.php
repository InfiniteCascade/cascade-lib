<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use teal\helpers\Html;

/**
 * AreaDecorator [[@doctodo class_description:cascade\components\web\widgets\decorator\AreaDecorator]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AreaDecorator extends Decorator
{
    /**
     * @var [[@doctodo var_type:panelCssClass]] [[@doctodo var_description:panelCssClass]]
     */
    public $panelCssClass = 'panel';
    /**
     * @var [[@doctodo var_type:panelStateCssClass]] [[@doctodo var_description:panelStateCssClass]]
     */
    public $panelStateCssClass = 'panel-default';
    /**
     * @var [[@doctodo var_type:gridCellClass]] [[@doctodo var_description:gridCellClass]]
     */
    public $gridCellClass = 'teal\web\grid\Cell';

    /**
     * @inheritdoc
     */
    public function generateStart()
    {
        Html::addCssClass($this->owner->htmlOptions, $this->owner->panelCssClass);
        Html::addCssClass($this->owner->htmlOptions, $this->owner->panelStateCssClass);

        return parent::generateStart();
    }

    /**
     * [[@doctodo method_description:generateHeader]].
     *
     * @return [[@doctodo return_type:generateHeader]] [[@doctodo return_description:generateHeader]]
     */
    public function generateHeader()
    {
        $parts = [];
        $parts[] = Html::beginTag('div', ['class' => 'panel-body']);

        return implode("", $parts);
    }

    /**
     * [[@doctodo method_description:generateFooter]].
     *
     * @return [[@doctodo return_type:generateFooter]] [[@doctodo return_description:generateFooter]]
     */
    public function generateFooter()
    {
        $parts = [];
        $parts[] = Html::endTag('div'); // panel-body

        return implode("", $parts);
    }

    /**
     * Get panel title.
     *
     * @return [[@doctodo return_type:getPanelTitle]] [[@doctodo return_description:getPanelTitle]]
     */
    public function getPanelTitle()
    {
        return $this->owner->parseText($this->owner->title);
    }
}
