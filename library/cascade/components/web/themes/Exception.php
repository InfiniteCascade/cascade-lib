<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

/**
 * Exception [[@doctodo class_description:cascade\components\web\themes\Exception]].
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
