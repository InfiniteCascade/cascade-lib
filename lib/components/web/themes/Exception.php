<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
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
