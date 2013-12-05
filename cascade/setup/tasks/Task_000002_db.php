<?php
namespace cascade\setup\tasks;

use infinite\setup\Exception;

class Task_000002_db extends \infinite\setup\Task {
	protected $_migrator;
	
	public function getTitle() {
		return 'Database';
	}

	public function test() {
		$request = $this->migrator->getRequest();
		$request->setParams(['migrate/new', '--interactive=0', 1000]);
		list($route, $params) = $request->resolve();
        ob_start();
        $this->migrator->run();
        $result = ob_get_clean();
        preg_match('/Found ([0-9]+) new migration/',$result, $matches);
        if (empty($matches[1])) {
        	return true;
        }
        $numberMatches = (int)$matches[1];
		return $numberMatches === 0;
	}

	public function run() {
		$request = $this->migrator->getRequest();
		$request->setParams(['migrate', '--interactive=0']);
        ob_start();
        $this->migrator->run();
        $result = ob_get_clean();
        return preg_match('/Migrated up successfully./', $result) === 1;
	}

	public function getMigrator() {
		if (is_null($this->_migrator)) {
			$configFile = $this->setup->environmentPath . DIRECTORY_SEPARATOR . 'console.php';
			if (!is_file($configFile)) {
				throw new Exception("Invalid console config path: {$configFile}");
			}
			$config = require($configFile);
			//var_dump($config);exit;
			$this->_migrator = new \infinite\console\Application($config);
		}
		return $this->_migrator;
	}
	
	public function getVerification() {
		if (!$this->test()) {
			return 'There are database upgrades available. Would you like to upgrade the database now?';
		}
		return false;
	}
}
?>