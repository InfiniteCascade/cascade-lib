<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\section;

/**
 * SideSection [@doctodo write class description for SideSection]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class SideSection extends Section
{
    public function init()
    {
        parent::init();
        $this->title = false;
        $this->icon = false;
    }

    public function getWidgetDecoratorClass()
    {
        return 'cascade\\components\\web\\widgets\\decorator\\EmbeddedDecorator';
    }

    public function widgetCellSettings()
    {
        return [
            'mediumDesktopColumns' => 12,
            'tabletColumns' => 12,
            'baseSize' => 'tablet'
        ];
    }

    public function isSingle()
    {
        return false;
    }

    public function defaultItems($parent = null)
    {
        $default = [];
        $default['_header'] = [
            'object' => [
                'class' => 'cascade\\components\\web\\widgets\\Item',
                'widget' => ['class' => 'cascade\\components\\web\\widgets\\base\\Header'],
            ]
        ];

        return $default;
    }
}
