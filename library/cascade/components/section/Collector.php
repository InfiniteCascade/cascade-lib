<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\section;

use cascade\components\web\widgets\section\SideSection;
use cascade\components\web\widgets\section\ParentSection;

/**
 * Collector [@doctodo write class description for Collector].
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
        return Item::className();
    }

    /**
     * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Section';
    }

    /**
     * @inheritdoc
     */
    public function getInitialItems()
    {
        return [
            '_side' => ['object' => ['class' => SideSection::className()], 'priority' => false],
            '_parents' => ['object' => ['class' => ParentSection::className()], 'priority' => 1000000, 'title' => 'Related'],
        ];
    }
}
