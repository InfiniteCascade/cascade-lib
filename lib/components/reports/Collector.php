<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\reports;

/**
 * Collector [[@doctodo class_description:cascade\components\reports\Collector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \canis\base\collector\Module
{
    /**
     * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return Item::className();
    }

    /**
     * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Report';
    }

    /**
     * Get all active.
     *
     * @return [[@doctodo return_type:getAllActive]] [[@doctodo return_description:getAllActive]]
     */
    public function getAllActive()
    {
        return $this->getAll();
    }
}
