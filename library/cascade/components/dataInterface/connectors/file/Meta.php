<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use infinite\base\exceptions\Exception;

/**
 * Meta [@doctodo write class description for Meta]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \infinite\base\Object
{

    protected $_sourceFile;
    protected $_attributes;
    protected $_interface;

    /**
     * @var __var__metas_type__ __var__metas_description__
     */
    static $_metas = [];

    /**
     * Get
     * @param __param_interface_type__    $interface    __param_interface_description__
     * @param __param_foreignTable_type__ $foreignTable __param_foreignTable_description__
     * @return __return_get_type__         __return_get_description__
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
     * __method_hasAttribute_description__
     * @param __param_name_type__          $name __param_name_description__
     * @return __return_hasAttribute_type__ __return_hasAttribute_description__
     */
    public function hasAttribute($name)
    {
        return in_array($name, $this->_attributes);
    }

    /**
     * Get attribute keys
     * @return __return_getAttributeKeys_type__ __return_getAttributeKeys_description__
     */
    public function getAttributeKeys()
    {
        return $this->_attributes;
    }


    /**
     * Set interface
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface
     * @return __return_getInterface_type__ __return_getInterface_description__
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
