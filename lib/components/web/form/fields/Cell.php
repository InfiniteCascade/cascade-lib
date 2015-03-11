<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\form\fields;

/**
 * Cell [[@doctodo class_description:cascade\components\web\form\fields\Cell]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Cell extends \teal\web\grid\Cell
{
    /**
     * @inheritdoc
     */
    public $baseSize = 'tablet';

    /**
     * @inheritdoc
     */
    public $phoneSize = false;
    /**
     * @inheritdoc
     */
    public $tabletSize = 'auto';
    /**
     * @inheritdoc
     */
    public $mediumDesktopSize = false; // baseline
    /**
     * @inheritdoc
     */
    public $largeDesktopSize = false;

    /**
     * @inheritdoc
     */
    protected $_tabletColumns = 'auto';
}
