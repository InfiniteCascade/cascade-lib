<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\reports;

/**
 * Collector [[@doctodo class_description:cascade\components\reports\Collector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \teal\base\collector\Module
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
