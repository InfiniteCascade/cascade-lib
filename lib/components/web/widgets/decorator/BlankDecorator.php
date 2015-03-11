<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

/**
 * BlankDecorator [[@doctodo class_description:cascade\components\web\widgets\decorator\BlankDecorator]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class BlankDecorator extends Decorator
{
    /**
     * @var [[@doctodo var_type:gridCellClass]] [[@doctodo var_description:gridCellClass]]
     */
    public $gridCellClass = 'teal\web\grid\Cell';

    /**
     * [[@doctodo method_description:generateHeader]].
     *
     * @return [[@doctodo return_type:generateHeader]] [[@doctodo return_description:generateHeader]]
     */
    public function generateHeader()
    {
        return;
    }

    /**
     * [[@doctodo method_description:generateFooter]].
     *
     * @return [[@doctodo return_type:generateFooter]] [[@doctodo return_description:generateFooter]]
     */
    public function generateFooter()
    {
        return;
    }
}
