<?php
namespace cascade\setup\tasks;

use infinite\setup\Exception;

class Task_000006_acl extends AclTask {
	public function getBaseRules() {
		return [
		// @todo add primary account
		];
	}
}
?>