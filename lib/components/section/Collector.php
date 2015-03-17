<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\section;

use cascade\components\web\widgets\section\ParentSection;
use cascade\components\web\widgets\section\SideSection;

/**
 * Collector [[@doctodo class_description:cascade\components\section\Collector]].
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
