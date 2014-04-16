<?php
namespace cascade\components\web\widgets\section;

class ParentSection extends Section
{
    public function init()
    {
        parent::init();
        $this->title = 'Related';
        $this->icon = false;
    }

    public function widgetCellSettings()
    {
        return [
            'mediumDesktopColumns' => 6,
            'tabletColumns' => 6,
            'baseSize' => 'tablet'
        ];
    }

    public function isSingle()
    {
        return false;
    }
}
