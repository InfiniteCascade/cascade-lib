<?php
/**
 * ./app/components/sections/RSectionModule.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\section;

use Yii;

use infinite\base\language\Noun;
use infinite\base\exceptions\Exception;
use infinite\base\exceptions\HttpException;
use infinite\helpers\ArrayHelper;
use infinite\helpers\Inflector;
use \cascade\components\helpers\StringHelper;

class Module extends \cascade\components\base\CollectorModule implements SectionInterface {
	use SectionTrait;
	
	public $version = 1;
	public $priority = 1000; //lower is better


	public function getModuleType() {
		return 'Section';
	}

	public function getCollectorName() {
		return 'sections';
	}

	public function setTitle($value) {
		$this->_title = $value;
	}


}


?>
