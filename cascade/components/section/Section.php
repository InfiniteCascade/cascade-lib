<?php
/**
 * ./app/components/sections/RSectionModule.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\section;

use Yii;

use infinite\base\collector\CollectedObjectTrait;

class Section extends \infinite\base\Object implements SectionInterface, \infinite\base\collector\CollectedObjectInterface {
	use CollectedObjectTrait;
	use SectionTrait;
	
	protected $_systemId;
	
	/**
	 *
	 *
	 * @param unknown $value
	 */
	public function setSystemId($value) {
		$this->_systemId = $value;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getSystemId() {
		return $this->_systemId;
	}
}