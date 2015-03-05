<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * Collector [[@doctodo class_description:cascade\components\dataInterface\Collector]].
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
        return 'Interface';
    }

    /**
     * Get by pk.
     *
     * @return [[@doctodo return_type:getByPk]] [[@doctodo return_description:getByPk]]
     */
    public function getByPk($pk)
    {
        foreach ($this->getAll() as $interface) {
            if ($interface->interfaceObject->primaryKey === $pk) {
                return $interface;
            }
        }

        return false;
    }
}
