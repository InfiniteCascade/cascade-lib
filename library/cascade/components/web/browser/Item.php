<?php
namespace cascade\components\web\browser;

class Item extends \infinite\web\browser\Item
{
    public $objectType = false;

    public function package()
    {
        return parent::package() + [
            'objectType' => $this->objectType
        ];
    }
}
