<?php
namespace cascade\components\security;

use Yii;

use infinite\base\exceptions\Exception;
use infinite\security\AuthorityInterface;

class Gatekeeper extends \infinite\security\Gatekeeper {

	public function setAuthority($authority)
	{
		if (!isset($authority['type']) 
			|| !($authorityTypeItem = Yii::$app->collectors['type']->getOne($authority['type'])))
			|| !($authorityType = $authorityTypeItem->object)
			|| !($authorityType instanceof AuthorityHandlerInterface)
		{
			throw new Exception("Access Control Authority is not set up correctly!");
		}
	}

	public function getAuthority()
	{
		if (is_null($this->_authority)) {
			$this->authority = ['type' => 'User'];
		}
		return $this->_authority;
	}
}
?>