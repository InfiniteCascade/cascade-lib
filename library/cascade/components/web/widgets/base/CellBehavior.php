<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

/**
 * CellBehavior [@doctodo write class description for CellBehavior]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class CellBehavior extends \yii\base\Behavior
{
    /**
     * @var __var_gridCellClass_type__ __var_gridCellClass_description__
     */
    public $gridCellClass = 'infinite\web\grid\Cell';
    /**
     * @var __var__gridCell_type__ __var__gridCell_description__
     */
    protected $_gridCell;

    /**
     * Get grid cell settings
     * @return __return_getGridCellSettings_type__ __return_getGridCellSettings_description__
     */
    public function getGridCellSettings()
    {
        return [
            'columns' => 12,
            'maxColumns' => 12,
            'tabletSize' => false
        ];
    }

    /**
     * Get cell
     * @return __return_getCell_type__ __return_getCell_description__
     */
    public function getCell()
    {
        if (is_null($this->_gridCell)) {
            $gridCellClass = $this->owner->gridCellClass;
            $objectSettings = $this->owner->gridCellSettings;
            $objectSettings['class'] = $gridCellClass;
            $objectSettings['content'] = $this->owner->cellContent;
            $this->_gridCell = Yii::createObject($objectSettings);
        }

        return $this->_gridCell;
    }

    /**
     * Get cell content
     * @return __return_getCellContent_type__ __return_getCellContent_description__
     */
    public function getCellContent()
    {
        return $this->owner;
    }
}
