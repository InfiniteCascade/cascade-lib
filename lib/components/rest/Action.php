<?php
namespace cascade\components\rest;

use Yii;

/**
 * Action [[@doctodo class_description:cascade\components\rest\Action]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Action extends \yii\rest\Action
{
    use ActionTrait;

    const EVENT_BEFORE_RUN = 'eventBeforeRun';
    const EVENT_AFTER_RUN = 'eventAfterRun';
}
