<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form\fields;

/**
 * Cell [@doctodo write class description for Cell]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Cell extends \infinite\web\grid\Cell
{
    public $baseSize = 'tablet';

    public $phoneSize = false;
    public $tabletSize = 'auto';
    public $mediumDesktopSize = false; // baseline
    public $largeDesktopSize = false;

    protected $_tabletColumns = 'auto';
}
