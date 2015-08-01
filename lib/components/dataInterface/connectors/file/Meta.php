<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface\connectors\file;

/**
 * Meta meta for file models (@todo determine if used?).
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \canis\base\Object
{
    /**
     * @var SourceFile the source file referenced in this meta
     */
    protected $_sourceFile;
    /**
     * @var array attribute names
     */
    protected $_attributes;
    /**
     * @var InterfaceItem the interface for this meta
     */
    protected $_interface;

    /**
     * @var array static collection of metas
     */
    public static $_metas = [];

    /**
     * Get.
     *
     * @param InterfaceItem  $interface  the interface object
     * @param Source $sourceFile the source file
     *
     * @return Meta the prepared meta object
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

    /**
     * Get source file.
     *
     * @return Source the source file
     */
    public function getSourceFile()
    {
        return $this->_sourceFile;
    }

    /**
     * Check if a specific attribute exists.
     *
     * @param string $name attribute name
     *
     * @return bool true if attribute exists, false otherwise
     */
    public function hasAttribute($name)
    {
        return in_array($name, $this->_attributes);
    }

    /**
     * Get attribute keys.
     *
     * @return array array of attribute names
     */
    public function getAttributeKeys()
    {
        return $this->_attributes;
    }

    /**
     * Set interface.
     *
     * @param InterfaceItem $value interface item for meta
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface.
     *
     * @return InterfaceItem the interface item for meta
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
