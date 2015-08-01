<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\widgets\base;

interface ObjectWidgetInterface
{
    public function getSortBy();
    public function getHeaderMenu();
    public function renderItemMenu($model, $key, $index);
}
