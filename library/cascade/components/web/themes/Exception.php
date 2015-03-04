<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

/**
 * Exception [@doctodo write class description for Exception].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Exception extends \infinite\base\exceptions\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Theme';
    }
}
