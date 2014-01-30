<?php
namespace cascade\components\security;

use Yii;

use infinite\base\exceptions\Exception;
// use infinite\security\AuthorityInterface;
use cascade\modules\core\TypeAccount\models\ObjectAccount;

class Gatekeeper extends \infinite\security\Gatekeeper {
	public function getPrimaryAccount() {
		return ObjectAccount::get(Yii::$app->params['primaryAccount']);
	}

	public function setAuthority($authority)
	{
		if (!isset($authority['type']) 
			|| !($authorityTypeItem = Yii::$app->collectors['types']->getOne($authority['type']))
			|| !($authorityType = $authorityTypeItem->object))
		{
			throw new Exception("Access Control Authority is not set up correctly!");
		}
		unset($authority['type']);
		$authority['handler'] = $authorityType;
		return parent::setAuthority($authority);
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