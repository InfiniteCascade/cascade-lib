<?php
namespace cascade\components\web\widgets\base;

interface ObjectWidgetInterface
{
    public function getSortBy();
    public function getHeaderMenu();
    public function renderItemMenu($model, $key, $index);
}
