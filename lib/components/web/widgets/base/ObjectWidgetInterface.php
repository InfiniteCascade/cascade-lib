<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\base;

interface ObjectWidgetInterface
{
    public function getSortBy();
    public function getHeaderMenu();
    public function renderItemMenu($model, $key, $index);
}
