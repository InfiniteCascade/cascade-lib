<?php
namespace cascade\setup\tasks;

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
