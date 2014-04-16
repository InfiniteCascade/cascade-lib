<?php
namespace cascade\components\db\behaviors\auditable;

use Yii;
use infinite\helpers\ArrayHelper;

class Auditable extends \infinite\db\behaviors\auditable\Auditable
{
    public $deleteEventClass = 'cascade\\components\\db\\behaviors\\auditable\\DeleteEvent';
	
}
?>