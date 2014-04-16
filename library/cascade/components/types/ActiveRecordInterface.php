<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

interface ActiveRecordInterface
{
    public function getUrl($action = 'view');
    public function getDefaultValues();
}
