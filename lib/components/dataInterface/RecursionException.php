<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\dataInterface;

/**
 * RecursionException [[@doctodo class_description:cascade\components\dataInterface\RecursionException]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RecursionException extends \canis\base\exceptions\Exception
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
