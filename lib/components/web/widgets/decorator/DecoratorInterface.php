<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\widgets\decorator;

interface DecoratorInterface
{
    public function generateStart();
    public function generateEnd();
}
