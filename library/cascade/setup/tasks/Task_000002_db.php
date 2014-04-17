<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

use infinite\setup\Exception;

/**
 * Task_000002_db [@doctodo write class description for Task_000002_db]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Task_000002_db extends \infinite\setup\Task
{
    protected $_migrator;
    public $skipComplete = true;

    public function getTitle()
    {
        return 'Database';
    }

    public function skip()
    {
        return parent::skip() && $this->setup->markDbReady();
    }

    public function test()
    {
        if ($this->isNewInstall()) { return false; }
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
        $numberMatches = (int) $matches[1];

        return $numberMatches === 0;
    }

    public function isNewInstall()
    {
        if (count($this->setup->app()->db->schema->tableNames) < 2) {
            return true;
        }

        return false;
    }

    public function run()
    {
        $request = $this->migrator->getRequest();
        $request->setParams(['migrate', '--interactive=0']);
        ob_start();
        $this->migrator->run();
        $result = ob_get_clean();

        return preg_match('/Migrated up successfully./', $result) === 1;
    }

    public function getMigrator()
    {
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

    public function getVerification()
    {
        if (!$this->isNewInstall() && !$this->test()) {
            return 'There are database upgrades available. Would you like to upgrade the database now?';
        }

        return false;
    }
}
