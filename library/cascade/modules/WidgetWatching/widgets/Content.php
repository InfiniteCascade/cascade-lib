<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\WidgetWatching\widgets;

class Content extends \cascade\components\web\widgets\Widget
{
    protected $_title = 'Watching';
    public $icon = 'fa-eye';

    public function getGridCellSettings()
    {
        $gridSettings = parent::getGridCellSettings();
        $gridSettings['columns'] = 6;
        $gridSettings['maxColumns'] = 12;

        return $gridSettings;
    }

    public function generateContent()
    {
        return 'noo<br ><br><br><br>hey';
    }
}
