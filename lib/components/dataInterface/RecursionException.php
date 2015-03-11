<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * RecursionException [[@doctodo class_description:cascade\components\dataInterface\RecursionException]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RecursionException extends \teal\base\exceptions\Exception
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
