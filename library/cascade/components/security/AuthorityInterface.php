<?php
namespace cascade\components\security;

interface AuthorityInterface extends \infinite\security\AuthorityInterface {
	public function getRequestorTypes();
}