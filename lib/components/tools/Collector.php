<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\tools;

/**
 * Collector [[@doctodo class_description:cascade\components\tools\Collector]].
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
        return 'Tool';
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
