<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\section;

/**
 * Collector [@doctodo write class description for Collector]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Collector extends \infinite\base\collector\Module
{
    public function getCollectorItemClass()
    {
        return '\cascade\components\section\Item';
    }

    public function getModulePrefix()
    {
        return 'Section';
    }

    public function getInitialItems()
    {
        return [
            '_side' => ['object' => ['class' => 'cascade\\components\\web\\widgets\\section\\SideSection'], 'priority' => false],
            '_parents' => ['object' => ['class' => 'cascade\\components\\web\\widgets\\section\\ParentSection'], 'priority' => 1000000, 'title' => 'Related']
        ];
    }
}
