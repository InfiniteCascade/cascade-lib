<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\fields;

/**
 * Taxonomy [[@doctodo class_description:cascade\components\db\fields\Taxonomy]].
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
     * @var [[@doctodo var_type:_moduleHandler]] [[@doctodo var_description:_moduleHandler]]
     */
    protected $_moduleHandler;
    /**
     * @var [[@doctodo var_type:taxonomy]] [[@doctodo var_description:taxonomy]]
     */
    public $taxonomy;

    /**
     * @var [[@doctodo var_type:_moduleHandlers]] [[@doctodo var_description:_moduleHandlers]]
     */
    protected static $_moduleHandlers = [];

    /**
     * Get module handler.
     *
     * @return [[@doctodo return_type:getModuleHandler]] [[@doctodo return_description:getModuleHandler]]
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
    /**
     * @inheritdoc
     */
    public function getFilterSettings()
    {
        return false;
    }
}
