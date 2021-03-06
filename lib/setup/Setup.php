<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\setup;

/**
 * Setup Perform the web setup for the application.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Setup extends \canis\setup\Setup
{
	public function getSetupTaskConfig()
	{
		$tasks = [];
		$tasks[] = [
			'class' => \canis\setup\tasks\Environment::className()
		];
		$tasks[] = [
			'class' => \canis\setup\tasks\Database::className()
		];
		$tasks[] = [
			'class' => tasks\Groups::className()
		];
		$tasks[] = [
			'class' => tasks\Acl::className()
		];
		$tasks[] = [
			'class' => tasks\Account::className()
		];
		$tasks[] = [
			'class' => tasks\AdminUser::className()
		];
		$tasks[] = [
			'class' => tasks\Collectors::className()
		];
		return $tasks;
	}
}
