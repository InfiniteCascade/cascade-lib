<?php
namespace cascade\components\dataInterface;

class RecursionException extends \infinite\base\exceptions\Exception
{
/**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Data Interface Recursion';
    }
}
