<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * Collector collector for the data interfaces.
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
        return 'Interface';
    }

    /**
     * Get by pk.
     *
     * @param mixed $pk the primary key
     *
     * @return Item data interface item
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
