<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * RecursionException [@doctodo write class description for RecursionException].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RecursionException extends \infinite\base\exceptions\Exception
{
    /**
     * Get name.
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Data Interface Recursion';
    }
}
