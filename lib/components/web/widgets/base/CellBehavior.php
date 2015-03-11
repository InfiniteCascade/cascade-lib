<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

/**
 * CellBehavior [[@doctodo class_description:cascade\components\web\widgets\base\CellBehavior]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class CellBehavior extends \yii\base\Behavior
{
    /**
     * @var [[@doctodo var_type:gridCellClass]] [[@doctodo var_description:gridCellClass]]
     */
    public $gridCellClass = 'teal\web\grid\Cell';
    /**
     * @var [[@doctodo var_type:_gridCell]] [[@doctodo var_description:_gridCell]]
     */
    protected $_gridCell;

    /**
     * Get grid cell settings.
     *
     * @return [[@doctodo return_type:getGridCellSettings]] [[@doctodo return_description:getGridCellSettings]]
     */
    public function getGridCellSettings()
    {
        return [
            'columns' => 12,
            'maxColumns' => 12,
            'tabletSize' => false,
        ];
    }

    /**
     * Get cell.
     *
     * @return [[@doctodo return_type:getCell]] [[@doctodo return_description:getCell]]
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
     * Get cell content.
     *
     * @return [[@doctodo return_type:getCellContent]] [[@doctodo return_description:getCellContent]]
     */
    public function getCellContent()
    {
        return $this->owner;
    }
}
