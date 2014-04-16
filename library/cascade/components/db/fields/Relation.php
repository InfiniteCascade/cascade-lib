<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

class Relation extends Base
{
    public $formFieldClass = 'cascade\components\web\form\fields\Relation';
    protected $_human = true;
    protected $_moduleHandler;
    public $relationship;
    public $modelRole; // either parent or child
    public $baseModel;
    static $_moduleHandlers = [];

    public function getCompanion()
    {
        if ($this->modelRole === 'parent') {
            return $this->relationship->child;
        } else {
            return $this->relationship->parent;
        }
    }
    public function getModule()
    {
        if ($this->modelRole === 'child') {
            return $this->relationship->child;
        } else {
            return $this->relationship->parent;
        }
    }

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

    public function hasFile()
    {
        return $this->companion->dummyModel->getBehavior('Storage') !== null;
    }

    public function getCompanionField()
    {
        $fieldParts = explode(':', $this->field);
        if ($this->modelRole === 'parent') {
            return 'child:'.$fieldParts[1];
        } else {
            return 'parent:'.$fieldParts[1];
        }
    }

    public function determineLocations()
    {
        if (!($this->modelRole === 'child' && !$this->relationship->isHasOne())
            &&	!($this->modelRole === 'parent')) {
            return [self::LOCATION_DETAILS];
        }

        return [self::LOCATION_HIDDEN];
    }
}
