<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\dataInterface;

/**
 * MissingItemException [[@doctodo class_description:cascade\components\dataInterface\MissingItemException]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class MissingItemException extends \canis\base\exceptions\Exception
{
    /**
     * Get name.
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Missing Data Item';
    }
}
