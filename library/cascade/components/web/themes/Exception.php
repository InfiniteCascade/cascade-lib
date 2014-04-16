<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

class Exception extends \infinite\base\exceptions\Exception
{
    public function getName()
    {
        return 'Theme';
    }
}
