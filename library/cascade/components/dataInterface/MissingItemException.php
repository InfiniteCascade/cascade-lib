<?php
namespace cascade\components\dataInterface;

class MissingItemException extends \infinite\base\exceptions\Exception
{
/**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Missing Data Item';
    }
}
