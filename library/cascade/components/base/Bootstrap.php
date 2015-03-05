<?php
namespace cascade\components\base;

use infinite\base\Cron;
use infinite\base\Daemon;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * Bootstrap [[@doctodo class_description:cascade\components\base\Bootstrap]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Bootstrap extends \yii\base\Object implements BootstrapInterface
{
    /**
     * [[@doctodo method_description:bootstrap]].
     */
    public function bootstrap($app)
    {

        // Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Preview::className(), 'cronNightly']);
        // Event::on(Cron::className(), Cron::EVENT_MONTHLY, [Preview::className(), 'cronMonthly']);
        // Event::on(Daemon::className(), Daemon::EVENT_TICK, [Preview::className(), 'daemonTick']);
        // Event::on(Daemon::className(), Daemon::EVENT_POST_TICK, [Preview::className(), 'daemonPostTick']);
    }
}
