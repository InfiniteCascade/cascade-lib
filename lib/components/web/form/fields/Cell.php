<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\form\fields;

/**
 * Cell [[@doctodo class_description:cascade\components\web\form\fields\Cell]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Cell extends \canis\web\grid\Cell
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
