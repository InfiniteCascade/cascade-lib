<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

/**
 * Task_000008_collectors [@doctodo write class description for Task_000008_collectors]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Task_000008_collectors extends \infinite\setup\Task
{
    public function getTitle()
    {
        return 'Collector Item Setup';
    }

    public function test()
    {
        return $this->setup->app()->collectors->areReady();
    }
    public function run()
    {
        return $this->setup->app()->collectors->initialize();
    }
    public function getFields()
    {
        return false;
    }
}
