<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\tools;

/**
 * Collector [@doctodo write class description for Collector]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \infinite\base\collector\Module
{
    /**
    * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return 'cascade\\components\\tools\\Item';
    }

    /**
    * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Tool';
    }

    
    
    public function getAllActive()
    {
        return $this->getAll();
    }
}
