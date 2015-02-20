<?php
namespace cascade\components\base;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Application;
use yii\base\Event;
use infinite\base\Daemon;
use infinite\base\Cron;

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