<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

/**
 * Taxonomy [@doctodo write class description for Taxonomy].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
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
     */
    protected $_moduleHandler;
    /**
     */
    public $taxonomy;

    /**
     */
    protected static $_moduleHandlers = [];

    /**
     * Get module handler.
     */
    public function getModuleHandler()
    {
        if (is_null($this->_moduleHandler)) {
            $stem = $this->field;
            if (!isset(self::$_moduleHandlers[$stem])) {
                self::$_moduleHandlers[$stem] = [];
            }
            $n = count(self::$_moduleHandlers[$stem]);
            $this->_moduleHandler = $this->field . ':_' . $n;
            self::$_moduleHandlers[$stem][] = $this->_moduleHandler;
        }

        return $this->_moduleHandler;
    }
    public function getFilterSettings()
    {
        return false;
    }
}
