<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\section;

/**
 * ParentSection [@doctodo write class description for ParentSection]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ParentSection extends Section
{
    /**
    * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = 'Related';
        $this->icon = false;
    }

    /**
    * @inheritdoc
     */
    public function widgetCellSettings()
    {
        return [
            'mediumDesktopColumns' => 6,
            'tabletColumns' => 6,
            'baseSize' => 'tablet'
        ];
    }

    /**
     * __method_isSingle_description__
     * @return __return_isSingle_type__ __return_isSingle_description__
     */
    public function isSingle()
    {
        return false;
    }
}
