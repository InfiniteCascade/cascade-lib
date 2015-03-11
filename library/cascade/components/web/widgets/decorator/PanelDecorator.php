<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use teal\helpers\Html;
use yii\bootstrap\Nav;

/**
 * PanelDecorator [[@doctodo class_description:cascade\components\web\widgets\decorator\PanelDecorator]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class PanelDecorator extends Decorator
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
     * [[@doctodo method_description:generatePanelTitle]].
     *
     * @return [[@doctodo return_type:generatePanelTitle]] [[@doctodo return_description:generatePanelTitle]]
     */
    public function generatePanelTitle()
    {
        $parts = [];
        if ($this->owner->title) {
            $menu = $icon = null;
            $titleMenu = $this->owner->generateTitleMenu();
            if ($titleMenu) {
                $menu = $titleMenu;
            }
            if (!empty($this->owner->icon)) {
                $icon = Html::tag('i', '', ['class' => 'panel-icon ' . $this->owner->icon]) . Html::tag('span', '', ['class' => 'break']);
            }
            $parts[] = Html::tag('div', Html::tag('h2', $icon . $this->owner->parseText($this->owner->title)) . $menu, ['class' => 'panel-heading']);
        }
        if (empty($parts)) {
            return false;
        }

        return implode("", $parts);
    }

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
        $title = $this->owner->generatePanelTitle();
        if ($title) {
            $parts[] = $title;
        }
        $parts[] = Html::beginTag('div', ['class' => 'panel-body']);

        return implode("", $parts);
    }

    /**
     * [[@doctodo method_description:generateTitleMenu]].
     *
     * @return [[@doctodo return_type:generateTitleMenu]] [[@doctodo return_description:generateTitleMenu]]
     */
    public function generateTitleMenu()
    {
        $menu = $this->owner->getHeaderMenu();
        if (empty($menu)) {
            return false;
        }
        $this->backgroundifyMenu($menu);

        return Nav::widget([
            'items' => $menu,
            'encodeLabels' => false,
            'options' => ['class' => 'pull-right nav-pills'],
        ]);
    }

    /**
     * [[@doctodo method_description:backgroundifyMenu]].
     *
     * @param [[@doctodo param_type:items]] $items [[@doctodo param_description:items]]
     *
     * @return [[@doctodo return_type:backgroundifyMenu]] [[@doctodo return_description:backgroundifyMenu]]
     */
    protected function backgroundifyMenu(&$items)
    {
        if (!is_array($items)) {
            return;
        }
        foreach ($items as $k => $v) {
            if (!isset($items[$k]['linkOptions'])) {
                $items[$k]['linkOptions'] = [];
            }
            if (!isset($items[$k]['linkOptions']['data-background'])) {
                $items[$k]['linkOptions']['data-handler'] = 'background';
            }
            if (isset($items[$k]['items'])) {
                $this->backgroundifyMenu($items[$k]['items']);
            }
        }
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
