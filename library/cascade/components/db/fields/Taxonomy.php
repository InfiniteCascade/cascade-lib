<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

/**
 * Taxonomy [@doctodo write class description for Taxonomy]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Taxonomy extends Base
{
    /**
     * @inheritdoc
     */
    public $formFieldClass = 'cascade\components\web\form\fields\Taxonomy';
    /**
     * @inheritdoc
     */
    protected $_human = true;
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    protected $_moduleHandler;
    /**
     * @var __var_taxonomy_type__ __var_taxonomy_description__
     */
    public $taxonomy;

    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    /**
     * @var __var__moduleHandlers_type__ __var__moduleHandlers_description__
     */
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    protected static $_moduleHandlers = [];

    /**
     * Get module handler
     * @return __return_getModuleHandler_type__ __return_getModuleHandler_description__
     */
    public function getModuleHandler()
    {
        if (is_null($this->_moduleHandler)) {
            $stem = $this->field;
            if (!isset(self::$_moduleHandlers[$stem])) { self::$_moduleHandlers[$stem] = []; }
            $n = count(self::$_moduleHandlers[$stem]);
            $this->_moduleHandler = $this->field .':_'. $n;
            self::$_moduleHandlers[$stem][] = $this->_moduleHandler;
        }

        return $this->_moduleHandler;
    }
}
