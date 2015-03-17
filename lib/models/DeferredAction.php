<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

/**
 * DeferredAction is the model class for table "deferred_action".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeferredAction extends \canis\db\models\DeferredAction
{
    use ActiveRecordTrait;
}
