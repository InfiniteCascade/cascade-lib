<?php
namespace cascade\components\web\themes;

class Exception extends \infinite\base\exceptions\Exception
{
    public function getName()
    {
        return 'Theme';
    }
}
