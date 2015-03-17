<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\types;

interface ActiveRecordInterface
{
    public function getUrl($action = 'view');
    public function getDefaultValues();
}
