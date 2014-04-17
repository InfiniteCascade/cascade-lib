<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

/**
 * BlankDecorator [@doctodo write class description for BlankDecorator]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class BlankDecorator extends Decorator
{
    public $gridCellClass = 'infinite\web\grid\Cell';

    public function generateHeader()
    {
        return null;
    }

    public function generateFooter()
    {
        return null;
    }
}
