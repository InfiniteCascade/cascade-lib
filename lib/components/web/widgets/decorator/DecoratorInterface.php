<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\decorator;

interface DecoratorInterface
{
    public function generateStart();
    public function generateEnd();
}
