<?php
/**
 * ./app/components/objects/fields/BaseFormat.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\db\fields;

abstract class BaseFormat extends \infinite\base\Object {
	public $field;
	abstract public function get();
}


?>
