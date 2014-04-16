<?php
namespace cascade\components\web\widgets\decorator;

class BlankDecorator extends Decorator
{
    public $gridCellClass = 'infinite\web\grid\Cell';

    public function generateHeader()
    {
        return null;
    }

    public function generateFooter()
    {
        return null;
    }
}
