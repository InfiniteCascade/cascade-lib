<?php
namespace cascade\components\rest;

use Yii;

class Action extends \yii\rest\Action
{
    use ActionTrait;

    const EVENT_BEFORE_RUN = 'eventBeforeRun';
    const EVENT_AFTER_RUN = 'eventAfterRun';
}
