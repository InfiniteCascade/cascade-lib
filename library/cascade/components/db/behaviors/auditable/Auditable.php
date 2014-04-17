<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * Auditable [@doctodo write class description for Auditable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Auditable extends \infinite\db\behaviors\auditable\Auditable
{
    public $deleteEventClass = 'cascade\\components\\db\\behaviors\\auditable\\DeleteEvent';

}
