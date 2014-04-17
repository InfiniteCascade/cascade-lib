<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;
use yii\bootstrap\Nav;

/**
 * PanelDecorator [@doctodo write class description for PanelDecorator]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class PanelDecorator extends Decorator
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
     * __method_generatePanelTitle_description__
     * @return __return_generatePanelTitle_type__ __return_generatePanelTitle_description__
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
                $icon = Html::tag('i', '', ['class' => 'panel-icon '. $this->owner->icon]) . Html::tag('span', '', ['class' => 'break']);
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
        $title = $this->owner->generatePanelTitle();
        if ($title) {
            $parts[] = $title;
        }
        $parts[] = Html::beginTag('div', ['class' => 'panel-body']);

        return implode("", $parts);
    }

    /**
     * __method_generateTitleMenu_description__
     * @return __return_generateTitleMenu_type__ __return_generateTitleMenu_description__
     */
    public function generateTitleMenu()
    {
        $menu = $this->owner->getHeaderMenu();
        if (empty($menu)) { return false; }
        $this->backgroundifyMenu($menu);

        return Nav::widget([
            'items' => $menu,
            'encodeLabels' => false,
            'options' => ['class' => 'pull-right nav-pills']
        ]);
    }

    /**
     * __method_backgroundifyMenu_description__
     * @param __param_items_type__              $items __param_items_description__
     * @return __return_backgroundifyMenu_type__ __return_backgroundifyMenu_description__
     */
    protected function backgroundifyMenu(&$items)
    {
        if (!is_array($items)) { return; }
        foreach ($items as $k => $v) {
            if (!isset($items[$k]['linkOptions'])) { $items[$k]['linkOptions'] = []; }
            if (!isset($items[$k]['linkOptions']['data-background'])) {
                $items[$k]['linkOptions']['data-handler'] = 'background';
            }
            if (isset($items[$k]['items'])) {
                $this->backgroundifyMenu($items[$k]['items']);
            }
        }
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
     * __method_getPanelTitle_description__
     * @return __return_getPanelTitle_type__ __return_getPanelTitle_description__
     */
    public function getPanelTitle()
    {
        return $this->owner->parseText($this->owner->title);
    }
}
