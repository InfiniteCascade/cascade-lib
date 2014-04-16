<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

class MissingItemException extends \infinite\base\exceptions\Exception
{
/**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Missing Data Item';
    }
}
