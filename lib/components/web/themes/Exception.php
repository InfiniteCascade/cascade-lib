<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\themes;

/**
 * Exception [[@doctodo class_description:cascade\components\web\themes\Exception]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Exception extends \teal\base\exceptions\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Theme';
    }
}
