<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\WidgetWatching\widgets;

/**
 * Content [@doctodo write class description for Content]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Content extends \cascade\components\web\widgets\Widget
{
    /**
     * @inheritdoc
     */
    protected $_title = 'Watching';
    /**
     * @inheritdoc
     */
    public $icon = 'fa-eye';

    /**
     * Get grid cell settings
     * @return __return_getGridCellSettings_type__ __return_getGridCellSettings_description__
     */
    public function getGridCellSettings()
    {
        $gridSettings = parent::getGridCellSettings();
        $gridSettings['columns'] = 6;
        $gridSettings['maxColumns'] = 12;

        return $gridSettings;
    }

    /**
    * @inheritdoc
     */
    public function generateContent()
    {
        return 'noo<br ><br><br><br>hey';
    }
}
