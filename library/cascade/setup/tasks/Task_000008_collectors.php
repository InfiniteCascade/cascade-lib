<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\setup\tasks;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Task_000008_collectors extends \teal\setup\Task
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Collector Item Setup';
    }

    /**
     * @inheritdoc
     */
    public function test()
    {
        return $this->setup->app()->collectors->areReady();
    }
    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->setup->app()->collectors->initialize();
    }
    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return false;
    }
}
