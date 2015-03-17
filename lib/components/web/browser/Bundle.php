<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
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
