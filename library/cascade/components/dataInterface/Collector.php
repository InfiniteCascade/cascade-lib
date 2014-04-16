<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

class Collector extends \infinite\base\collector\Module
{
    public function getCollectorItemClass()
    {
        return 'cascade\\components\\dataInterface\\Item';
    }

    public function getModulePrefix()
    {
        return 'Interface';
    }
}
