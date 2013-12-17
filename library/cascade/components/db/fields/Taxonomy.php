<?php
/**
 * ./app/components/objects/fields/Model.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields;


class Taxonomy extends Base {
	public $formFieldClass = 'cascade\components\web\form\fields\Taxonomy';
	protected $_human = true;
	protected $_moduleHandler;
	public $taxonomy;

	protected static $_moduleHandlers = [];


	public function getModuleHandler() {
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

?>