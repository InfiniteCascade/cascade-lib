<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
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
