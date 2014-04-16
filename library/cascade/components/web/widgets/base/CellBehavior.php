<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

class CellBehavior extends \yii\base\Behavior
{
    public $gridCellClass = 'infinite\web\grid\Cell';
    protected $_gridCell;

    public function getGridCellSettings()
    {
        return [
            'columns' => 12,
            'maxColumns' => 12,
            'tabletSize' => false
        ];
    }

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

    public function getCellContent()
    {
        return $this->owner;
    }
}
