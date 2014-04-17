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
    /**
     * @var __var__migrator_type__ __var__migrator_description__
     */
    protected $_migrator;
    /**
     * @inheritdoc
     */
    public $skipComplete = true;

    /**
    * @inheritdoc
    **/
    public function getTitle()
    {
        return 'Database';
    }

    /**
    * @inheritdoc
    **/
    public function skip()
    {
        return parent::skip() && $this->setup->markDbReady();
    }

    /**
    * @inheritdoc
    **/
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

    /**
     * __method_isNewInstall_description__
     * @return __return_isNewInstall_type__ __return_isNewInstall_description__
     */
    public function isNewInstall()
    {
        if (count($this->setup->app()->db->schema->tableNames) < 2) {
            return true;
        }

        return false;
    }

    /**
    * @inheritdoc
    **/
    public function run()
    {
        $request = $this->migrator->getRequest();
        $request->setParams(['migrate', '--interactive=0']);
        ob_start();
        $this->migrator->run();
        $result = ob_get_clean();

        return preg_match('/Migrated up successfully./', $result) === 1;
    }

    /**
     * __method_getMigrator_description__
     * @return __return_getMigrator_type__ __return_getMigrator_description__
     * @throws Exception                   __exception_Exception_description__
     */
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

    /**
    * @inheritdoc
    **/
    public function getVerification()
    {
        if (!$this->isNewInstall() && !$this->test()) {
            return 'There are database upgrades available. Would you like to upgrade the database now?';
        }

        return false;
    }
}
