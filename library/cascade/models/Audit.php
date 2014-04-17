<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * Audit is the model class for table "audit".
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Audit extends \infinite\db\models\Audit
{
    use ActiveRecordTrait;
}
