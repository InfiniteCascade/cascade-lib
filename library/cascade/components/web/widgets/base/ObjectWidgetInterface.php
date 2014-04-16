<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

interface ObjectWidgetInterface
{
    public function getSortBy();
    public function getHeaderMenu();
    public function renderItemMenu($model, $key, $index);
}
