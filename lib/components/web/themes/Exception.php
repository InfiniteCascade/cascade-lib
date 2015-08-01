<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\themes;

/**
 * Exception [[@doctodo class_description:cascade\components\web\themes\Exception]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Exception extends \canis\base\exceptions\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Theme';
    }
}
