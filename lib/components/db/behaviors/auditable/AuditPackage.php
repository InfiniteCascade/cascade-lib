<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\db\behaviors\auditable;

use ArrayIterator;

/**
 * AuditPackage [[@doctodo class_description:cascade\components\db\behaviors\auditable\AuditPackage]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AuditPackage extends \canis\base\Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * @var [[@doctodo var_type:similarThreshold]] [[@doctodo var_description:similarThreshold]]
     */
    public $similarThreshold = 21600;
    /**
     * @var [[@doctodo var_type:_items]] [[@doctodo var_description:_items]]
     */
    protected $_items = [];
    /**
     * @var [[@doctodo var_type:direction]] [[@doctodo var_description:direction]]
     */
    public $direction = '_older';
    /**
     * @var [[@doctodo var_type:context]] [[@doctodo var_description:context]]
     */
    public $context = false;

    /**
     * @inheritdoc
     */
    public function __construct($dataSource, $context = false)
    {
        foreach ($dataSource->models as $item) {
            $this->add($item);
        }
        $this->direction = $dataSource->direction;
        $this->context = $context;
        parent::__construct();
    }

    /**
     * [[@doctodo method_description:count]].
     *
     * @return [[@doctodo return_type:count]] [[@doctodo return_description:count]]
     */
    public function count()
    {
        return $this->getCount();
    }

    /**
     * Returns the number of items in the collection.
     *
     * @return integer the number of items in the collection.
     */
    public function getCount()
    {
        return count($this->_items);
    }

    /**
     * Returns the item with the specified name.
     *
     * @param string $name the item name
     *
     * @return object the item with the specified name. Null if the named item does not exist.
     *
     * @see getValue()
     */
    public function get($name)
    {
        return isset($this->_items[$name]) ? $this->_items[$name] : null;
    }

    /**
     * Returns whether there is a item with the specified name.
     * Note that if a item is marked for deletion from browser, this method will return false.
     *
     * @param string $name the item name
     *
     * @return boolean whether the named item exists
     *
     * @see remove()
     */
    public function has($name)
    {
        return isset($this->_items[$name]);
    }

    /**
     * Adds a item to the collection.
     * If there is already a item with the same name in the collection, it will be removed first.
     *
     * @param object $item the item to be added
     *
     * @throws InvalidCallException if the item collection is read only
     */
    public function add($item)
    {
        $this->_items[$item->primaryKey] = $item;
    }

    /**
     * Removes a item.
     * If `$removeFromBrowser` is true, the item will be removed from the browser.
     * In this case, a item with outdated expiry will be added to the collection.
     *
     * @param string $item the item object or the name of the item to be removed.
     *
     * @throws InvalidCallException if the item collection is read only
     */
    public function remove($item)
    {
        if (is_object($item)) {
            unset($this->_items[$item->primaryKey]);
        } else {
            unset($this->_items[$item]);
        }
    }

    /**
     * Removes all items.
     *
     * @throws InvalidCallException if the item collection is read only
     */
    public function removeAll()
    {
        $this->_items = [];
    }

    /**
     * Returns the collection as a PHP array.
     *               The array keys are item names, and the array values are the corresponding item objects.
     *
     * @return array the array representation of the collection.
     */
    public function toArray()
    {
        $threads = [];
        $p = [];
        $p['timestamp'] = time();
        $p['direction'] = $this->direction;
        $p['activity'] = [];
        $p['objects'] = [];
        $p['lastItem'] = null;
        $p['mostRecentItem'] = null;
        $lastKey = null;
        $lastTimestamp = null;
        foreach ($this as $item) {
            $eventObject = $item->eventObject;
            $eventObject->context = $this->context;
            $package = $eventObject->package;
            if (empty($p['lastItem']) || (float)$package['timestamp'] < $p['lastItem']) {
                $p['lastItem'] = (float)$package['timestamp'];
            }
            if (empty($p['mostRecentItem']) || (float)$package['timestamp'] > $p['mostRecentItem']) {
                $p['mostRecentItem'] = (float)$package['timestamp'];
            }
            if (!isset($threads[$package['key']])) {
                $threads[$package['key']] = 0;
            }
            if ($package['key'] !== $lastKey
                || (abs((float)$package['timestamp'] - $lastTimestamp) > ($this->similarThreshold))) {
                if (isset($threads[$lastKey])) {
                    $threads[$lastKey]++;
                }
                $lastKey = $package['key'];
                $lastTimestamp = (float)$package['timestamp'];
            }
            $key = $package['key'] . '-' . $threads[$lastKey];
            if (isset($p['activity'][$key])) {
                $p['activity'][$key]['details'][$item->primaryKey] = $package['details'];
                continue;
            }
            $p['activity'][$key] = [];
            $p['activity'][$key]['id'] = $item->primaryKey;
            $p['activity'][$key]['primaryObject'] = $package['primaryObject'];
            $p['activity'][$key]['agent'] = $package['agent'];
            $p['activity'][$key]['story'] = $package['story'];
            $p['activity'][$key]['timestamp'] = $package['timestamp'];
            $p['activity'][$key]['details'] = [$item->primaryKey => $package['details']];

            foreach ($package['objects'] as $object) {
                if (isset($p['objects'][$object->primaryKey])) {
                    continue;
                }
                $p['objects'][$object->primaryKey] = $object->package;
                $p['objects'][$object->primaryKey]['descriptor'] = htmlspecialchars(strip_tags($p['objects'][$object->primaryKey]['descriptor']));
                unset($p['objects'][$object->primaryKey]['id']);
            }
        }
        //\d($p);exit;
        return $p;
    }

    /**
     * Returns whether there is a item with the specified name.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `isset($collection[$name])`.
     *
     * @param string $name the item name
     *
     * @return boolean whether the named item exists
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Returns the item with the specified name.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `$item = $collection[$name];`.
     * This is equivalent to [[get()]].
     *
     * @param string $name the item name
     *
     * @return Object the item with the specified name, null if the named item does not exist.
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Adds the item to the collection.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `$collection[$name] = $item;`.
     * This is equivalent to [[add()]].
     *
     * @param string $name the item name
     * @param Object $item the item to be added
     */
    public function offsetSet($name, $item)
    {
        $this->add($item);
    }

    /**
     * Removes the named item.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `unset($collection[$name])`.
     * This is equivalent to [[remove()]].
     *
     * @param string $name the item name
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }

    /**
     * Get iterator.
     *
     * @return [[@doctodo return_type:getIterator]] [[@doctodo return_description:getIterator]]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }
}
