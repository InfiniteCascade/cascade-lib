<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\browser;

/**
 * Bundle [@doctodo write class description for Bundle].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Bundle extends \infinite\web\browser\Bundle
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
