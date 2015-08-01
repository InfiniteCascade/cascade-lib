<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\browser;

/**
 * Bundle [[@doctodo class_description:cascade\components\web\browser\Bundle]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Bundle extends \canis\web\browser\Bundle
{
    /**
     * @inheritdoc
     */
    public $itemClass = 'cascade\components\web\browser\Item';
    /**
     * @inheritdoc
     */
    public function getHandlers()
    {
        return [
            'types' => 'cascade\components\web\browser\HandlerTypes',
            'objects' => 'cascade\components\web\browser\HandlerObjects',
        ];
    }
}
