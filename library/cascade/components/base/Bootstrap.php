<?php
namespace cascade\components\base;

use infinite\base\Cron;
use infinite\base\Daemon;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;

class Bootstrap extends \yii\base\Object implements BootstrapInterface
{
    public function bootstrap($app)
    {

        // Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Preview::className(), 'cronNightly']);
        // Event::on(Cron::className(), Cron::EVENT_MONTHLY, [Preview::className(), 'cronMonthly']);
        // Event::on(Daemon::className(), Daemon::EVENT_TICK, [Preview::className(), 'daemonTick']);
        // Event::on(Daemon::className(), Daemon::EVENT_POST_TICK, [Preview::className(), 'daemonPostTick']);
    }
}
