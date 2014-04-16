<?php
namespace cascade\components\db\behaviors\auditable;

class Auditable extends \infinite\db\behaviors\auditable\Auditable
{
    public $deleteEventClass = 'cascade\\components\\db\\behaviors\\auditable\\DeleteEvent';

}
