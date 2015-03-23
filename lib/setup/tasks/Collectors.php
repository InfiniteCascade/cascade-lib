<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\setup\tasks;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collectors extends \canis\setup\tasks\BaseTask
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
