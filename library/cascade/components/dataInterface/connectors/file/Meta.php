<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

/**
 * Meta [@doctodo write class description for Meta].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \infinite\base\Object
{
    protected $_sourceFile;
    protected $_attributes;
    protected $_interface;

    /*
     */
    public static $_metas = [];

    /**
     * Get.
     */
    public static function get($interface, $sourceFile)
    {
        if (!isset(self::$_metas[$sourceFile->id])) {
            self::$_metas[$sourceFile->id] = new static($interface, $sourceFile);
        }

        return self::$_metas[$sourceFile->id];
    }

    /**
     * @inheritdoc
     */
    public function __construct($interface, $sourceFile)
    {
        $this->_sourceFile = $sourceFile;
        $this->_interface = $interface;
        $this->_attributes = $sourceFile->readLine(1);
    }

    public function getSourceFile()
    {
        return $this->_sourceFile;
    }

    /**
     *
     */
    public function hasAttribute($name)
    {
        return in_array($name, $this->_attributes);
    }

    /**
     * Get attribute keys.
     */
    public function getAttributeKeys()
    {
        return $this->_attributes;
    }

    /**
     * Set interface.
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface.
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
