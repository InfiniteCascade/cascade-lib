<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\types;

interface ActiveRecordInterface
{
    public function getUrl($action = 'view');
    public function getDefaultValues();
}
