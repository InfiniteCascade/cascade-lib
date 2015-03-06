<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

/**
 * Meta [[@doctodo class_description:cascade\components\dataInterface\connectors\file\Meta]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \infinite\base\Object
{
    /**
     * @var [[@doctodo var_type:_sourceFile]] [[@doctodo var_description:_sourceFile]]
     */
    protected $_sourceFile;
    /**
     * @var [[@doctodo var_type:_attributes]] [[@doctodo var_description:_attributes]]
     */
    protected $_attributes;
    /**
     * @var [[@doctodo var_type:_interface]] [[@doctodo var_description:_interface]]
     */
    protected $_interface;

    /**
     * @var [[@doctodo var_type:_metas]] [[@doctodo var_description:_metas]]
     */
    public static $_metas = [];

    /**
     * Get.
     *
     * @param [[@doctodo param_type:interface]]  $interface  [[@doctodo param_description:interface]]
     * @param [[@doctodo param_type:sourceFile]] $sourceFile [[@doctodo param_description:sourceFile]]
     *
     * @return [[@doctodo return_type:get]] [[@doctodo return_description:get]]
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
     * @return [[@doctodo return_type:getSourceFile]] [[@doctodo return_description:getSourceFile]]
     */
    public function getSourceFile()
    {
        return $this->_sourceFile;
    }

    /**
     * [[@doctodo method_description:hasAttribute]].
     *
     * @param [[@doctodo param_type:name]] $name [[@doctodo param_description:name]]
     *
     * @return [[@doctodo return_type:hasAttribute]] [[@doctodo return_description:hasAttribute]]
     */
    public function hasAttribute($name)
    {
        return in_array($name, $this->_attributes);
    }

    /**
     * Get attribute keys.
     *
     * @return [[@doctodo return_type:getAttributeKeys]] [[@doctodo return_description:getAttributeKeys]]
     */
    public function getAttributeKeys()
    {
        return $this->_attributes;
    }

    /**
     * Set interface.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setInterface($value)
    {
        $this->_interface = $value;
    }

    /**
     * Get interface.
     *
     * @return [[@doctodo return_type:getInterface]] [[@doctodo return_description:getInterface]]
     */
    public function getInterface()
    {
        return $this->_interface;
    }
}
