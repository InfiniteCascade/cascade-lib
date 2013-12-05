<?php
/**
 * ./app/components/objects/fields/BaseFormat.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields;

abstract class BaseFormat extends \infinite\base\Object {
	protected $_field;
	public function __construct($parent) {
		$this->_field = $parent;
	}

	abstract public function get();
}


?>
