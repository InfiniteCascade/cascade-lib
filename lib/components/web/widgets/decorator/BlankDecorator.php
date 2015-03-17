<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
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
    public $gridCellClass = 'canis\web\grid\Cell';

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
