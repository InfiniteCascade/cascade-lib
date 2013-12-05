<?php
/**
 * ./protected/components/web/request/RHttpRequest.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web;

use Yii;

use \cascade\models\Registry;
use yii\web\Application;

class Request extends \infinite\web\Request {
	protected $_object;
	protected $_parent;

	public function init() {
		parent::init();
		Yii::$app->on(Application::EVENT_BEFORE_REQUEST, array($this, 'startRequest'));
	}

	public function startRequest() {
		if (isset($_GET['parent'])) {
			$this->parent = Registry::getObject($_GET['parent']);
		}	
	}

	public function setObject($object) {
		$this->_object = $object;
	}

	public function getObject() {
		return $this->_object;
	}

	public function setParent($object) {
		$this->_parent = $object;
	}

	public function getParent() {
		return $this->_parent;
	}
}


?>
