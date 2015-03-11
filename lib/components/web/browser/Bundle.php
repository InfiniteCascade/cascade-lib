<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\browser;

/**
 * Bundle [[@doctodo class_description:cascade\components\web\browser\Bundle]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Bundle extends \teal\web\browser\Bundle
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
