<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\types;

interface ActiveRecordInterface
{
    public function getUrl($action = 'view');
    public function getDefaultValues();
}
