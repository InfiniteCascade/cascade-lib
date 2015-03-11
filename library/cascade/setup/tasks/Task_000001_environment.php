<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\setup\tasks;

use teal\setup\Exception;
use yii\helpers\Inflector;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Task_000001_environment extends \teal\setup\Task
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Environment';
    }

    /**
     * @inheritdoc
     */
    public function test()
    {
        if ($this->setup->isEnvironmented) {
            try {
                $oe = ini_set('display_errors', 0);
                $dbh = new \PDO('mysql:host=' . TEAL_APP_DATABASE_HOST . ';port=' . TEAL_APP_DATABASE_PORT . ';dbname=' . TEAL_APP_DATABASE_DBNAME, TEAL_APP_DATABASE_USERNAME, TEAL_APP_DATABASE_PASSWORD);
                ini_set('display_errors', $oe);
            } catch (\Exception $e) {
                throw new Exception("Unable to connect to database! Please verify your settings in <code>env.php</code>.");
            }
        }

        return $this->setup->isEnvironmented && $this->setup->version <= $this->setup->instanceVersion;
    }

    /**
     *
     */
    public function generateSalt($max = 120)
    {
        $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?";
        $i = 0;
        $salt = "";
        while ($i < $max) {
            $salt .= $characterList{mt_rand(0, (strlen($characterList) - 1))};
            $i++;
        }

        return $salt;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->fields) {
            $input = $this->input;
            $upgrade = false;
        } else {
            $input = [];
            $input['general'] = [];
            $input['general']['template'] = TEAL_APP_ENVIRONMENT;
            $input['general']['application_name'] = $this->setup->app()->name;

            $input['database'] = [];
            $input['database']['host'] = TEAL_APP_DATABASE_HOST;
            $input['database']['port'] = TEAL_APP_DATABASE_PORT;
            $input['database']['username'] = TEAL_APP_DATABASE_USERNAME;
            $input['database']['password'] = TEAL_APP_DATABASE_PASSWORD;
            $input['database']['dbname'] = TEAL_APP_DATABASE_DBNAME;
            $upgrade = true;
        }
        $input['_'] = [];
        $input['_']['yiiDebug'] = ($input['general']['template'] === 'development') ? 'true' : 'false';
        $input['_']['yiiTraceLevel'] = ($input['general']['template'] === 'development') ? '3' : '0';
        $input['_']['yiiEnv'] = ($input['general']['template'] === 'development') ? 'dev' : 'prod';
        $input['_']['version'] = $this->setup->version;
        $input['_']['application_id'] = self::generateId($input['general']['application_name']);
        $input['_']['envPath'] = $this->setup->configPath . DIRECTORY_SEPARATOR . $input['general']['template'];
        $input['_']['envPathFriendly'] = '__DIR__ . DIRECTORY_SEPARATOR . \'' . $input['general']['template'] . '\'';
        if ($this->setup->app()) {
            $input['_']['salt'] = $this->setup->app()->params['salt'];
            $input['_']['cookie_salt'] = $this->setup->app()->request['cookieValidationKey'];
        }
        if (empty($input['_']['salt'])) {
            $input['_']['salt'] = $this->generateSalt();
        }
        if (empty($input['_']['cookie_salt'])) {
            $input['_']['cookie_salt'] = $this->generateSalt(60);
        }

        if (!is_dir($input['_']['envPath'])) {
            @mkdir($input['_']['envPath'], 0700, true);
        }

        if (!is_dir($input['_']['envPath'])) {
            throw new Exception("Unable to create new environment directory {$input['_']['envPath']}");
        }

        // primary environment file
        $templateFile = $this->setup->environmentTemplateFilePath;
        if (!is_file($templateFile)) {
            throw new Exception("Invalid environment template file {$templateFile}");
        }
        $template = self::parseText(file_get_contents($templateFile), $input);
        file_put_contents($this->setup->environmentFilePath, $template);
        if (!file_exists($this->setup->environmentFilePath)) {
            $this->errors[] = 'Unable to create environment file {$this->setup->environmentFilePath}';

            return false;
        }
        if ($upgrade) {
            return true;
        }

        $templatePath = $this->setup->environmentTemplatesPath . DIRECTORY_SEPARATOR . $input['general']['template'];
        $files = ['web.php', 'web-test.php', 'redis.php', 'console.php', 'collectors.php', 'database.php', 'modules.php', 'params.php', 'import.php', 'roles.php', 'identityProviders.php', 'client_script.php'];
        foreach ($files as $file) {
            $templateFilePath = $templatePath . DIRECTORY_SEPARATOR . $file;
            if (!is_file($templateFilePath)) {
                continue;
            }
            $envFilePath = $input['_']['envPath'] . DIRECTORY_SEPARATOR . $file;
            $template = self::parseText(file_get_contents($templateFilePath), $input);
            file_put_contents($envFilePath, $template);
            if (!file_exists($envFilePath)) {
                $this->errors[] = 'Unable to create environment file {$envFilePath}';

                return false;
            }
        }

        return true;
    }

    /**
     *
     */
    public static function generateId($name)
    {
        return strtolower(Inflector::slug($name));
    }

    /**
     * @inheritdoc
     */
    public function loadInput($input)
    {
        if (!parent::loadInput($input)) {
            return false;
        }
        try {
            $oe = ini_set('display_errors', 0);
            $dbh = new \PDO('mysql:host=' . $this->input['database']['host'] . ';port=' . $this->input['database']['port'] . ';dbname=' . $this->input['database']['dbname'], $this->input['database']['username'], $this->input['database']['password']);
            ini_set('display_errors', $oe);
        } catch (Exception $e) {
            $fieldId = 'field_' . $this->id . '_database_host';
            $this->fieldErrors[$fieldId] = 'Error connecting to database: ' . $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Get env options.
     */
    public function getEnvOptions()
    {
        $envs = [];
        $templatePath = $this->setup->environmentTemplatesPath;
        $o = opendir($templatePath);
        while (($file = readdir($o)) !== false) {
            $path = $templatePath . DIRECTORY_SEPARATOR . $file;
            if (substr($file, 0, 1) === '.' or !is_dir($path)) {
                continue;
            }
            $envs[$file] = $path;
        }
        //var_dump($envs);
        return $envs;
    }

    /**
     * Get env list options.
     */
    public function getEnvListOptions()
    {
        $options = $this->envOptions;
        $list = [];
        foreach ($options as $k => $v) {
            $list[$k] = ucwords($k);
        }

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        if ($this->setup->isEnvironmented and $this->setup->app()) {
            return false;
        }

        $fields = [];
        $fields['general'] = ['label' => 'General', 'fields' => []];
        $fields['general']['fields']['template'] = ['type' => 'select', 'options' => $this->envListOptions, 'label' => 'Environment', 'required' => true, 'value' => function () { return defined('TEAL_APP_ENVIRONMENT') ? TEAL_APP_ENVIRONMENT : 'development'; }];
        $fields['general']['fields']['application_name'] = ['type' => 'text', 'label' => 'Application Name', 'required' => true, 'value' => function () { return $this->setup->name; }];

        $fields['database'] = ['label' => 'Database', 'fields' => []];
        $fields['database']['fields']['host'] = ['type' => 'text', 'label' => 'Host', 'required' => true, 'value' => function () { return defined('TEAL_APP_DATABASE_HOST') ? TEAL_APP_DATABASE_HOST : '127.0.0.1'; }];
        $fields['database']['fields']['port'] = ['type' => 'text', 'label' => 'Port', 'required' => true, 'value' => function () { return defined('TEAL_APP_DATABASE_PORT') ? TEAL_APP_DATABASE_PORT : '3306'; }];
        $fields['database']['fields']['username'] = ['type' => 'text', 'label' => 'Username', 'required' => true, 'value' => function () { return defined('TEAL_APP_DATABASE_USERNAME') ? TEAL_APP_DATABASE_USERNAME : ''; }];
        $fields['database']['fields']['password'] = ['type' => 'text', 'label' => 'Password', 'required' => true, 'value' => function () { return defined('TEAL_APP_DATABASE_PASSWORD') ? '' : ''; }];
        $fields['database']['fields']['dbname'] = ['type' => 'text', 'label' => 'Database Name', 'required' => true, 'value' => function () { return defined('TEAL_APP_DATABASE_DBNAME') ? TEAL_APP_DATABASE_DBNAME : ''; }];

        return $fields;
    }
}
