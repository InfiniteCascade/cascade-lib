<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\browser;

class Bundle extends \infinite\web\browser\Bundle
{
    public $itemClass = 'cascade\\components\\web\\browser\\Item';
    public function getHandlers()
    {
        return [
            'types' => 'cascade\\components\\web\\browser\\HandlerTypes',
            'objects' => 'cascade\\components\\web\\browser\\HandlerObjects'
        ];
    }
}
