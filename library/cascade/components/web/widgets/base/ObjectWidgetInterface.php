<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\widgets\base;

interface ObjectWidgetInterface
{
    public function getSortBy();
    public function getHeaderMenu();
    public function renderItemMenu($model, $key, $index);
}
