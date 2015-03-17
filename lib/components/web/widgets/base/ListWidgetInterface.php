<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\base;

interface ListWidgetInterface
{
    public function renderItem($model, $key, $index);
    public function getListItemOptions($model, $key, $index);
    public function renderItemContent($model, $key, $index);
    public function renderItemMenu($model, $key, $index);
    public function getMenuItems($model, $key, $index);
    public function getDataProvider();
    public function getDataProviderSettings();
    public function generateContent();
    public function getPaginationSettings();
}
