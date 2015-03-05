<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2015 Infinite Cascade
 * @license http://www.infinitecascade.com/license
 */

namespace cascade\commands;

use infinite\caching\Cacher;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * ToolsController [[@doctodo class_description:cascade\commands\ToolsController]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ToolsController extends \infinite\console\Controller
{
    /**
     * [[@doctodo method_description:actionFlush]].
     */
    public function actionFlush($category = null)
    {
        if (is_null($category)) {
            $category = $this->prompt("Category (blank for all): ");
        }
        if (empty($category)) {
            $category = 'all';
        } else {
            $category = ['category', $category];
        }
        Cacher::invalidateGroup($category);
        $this->out("Done!");
    }

    /**
     * [[@doctodo method_description:actionFixProject]].
     */
    public function actionFixProject()
    {
        $dirs = [
            Yii::getAlias('@cascade'),
            Yii::getAlias('@infinite'),
            Yii::getAlias('@cascade/modules/core'),
            Yii::getAlias('@psesd/cascade'),
            Yii::getAlias('@infinite/deferred'),
            Yii::getAlias('@infinite/notification'),
        ];
        $customStart = microtime(true);
        Console::stdout("Running custom fixes..." . PHP_EOL);
        foreach ($dirs as $dir) {
            $dirStart = microtime(true);
            $changed = 0;
            Console::stdout("\t" . $dir . "...");
            $files = FileHelper::findFiles($dir, ['only' => ['*.php'], 'recursive' => true]);
            Console::stdout("found " . count($files) . " files...");
            foreach ($files as $file) {
                if ($this->fixFile($file)) {
                    $changed++;
                }
            }
            Console::stdout("changed {$changed} files in " . round(microtime(true)-$dirStart, 1) . "s!" . PHP_EOL);
        }
        Console::stdout("done in " . round(microtime(true)-$customStart, 1) . "s!" . PHP_EOL . PHP_EOL);

        $phpcsStart = microtime(true);
        Console::stdout("Running style fixes..." . PHP_EOL);
        foreach ($dirs as $dir) {
            $dirStart = microtime(true);
            $changed = 0;
            Console::stdout("\t" . $dir . "...");
            $configFiles = [];
            $configFiles[] = $dir . DIRECTORY_SEPARATOR . '.php_cs';
            $configFiles[] = dirname($dir) . DIRECTORY_SEPARATOR . '.php_cs';
            $configFiles[] = dirname(dirname($dir)) . DIRECTORY_SEPARATOR . '.php_cs';
            $foundConfig = false;
            foreach ($configFiles as $configFile) {
                if (file_exists($configFile)) {
                    $foundConfig = $configFile;
                    break;
                }
            }
            if (!$foundConfig) {
                Console::stdout("skipped!" . PHP_EOL);
                continue;
            }
            $phpcsBinary = Yii::getAlias('@vendor/bin/php-cs-fixer');
            if (!file_exists($phpcsBinary)) {
                Console::stdout("no php-cs-fixer binary!" . PHP_EOL);
                continue;
            }

            $command = [];
            $command[] = PHP_BINARY;
            $command[] = $phpcsBinary;
            $command[] = '--no-interaction';
            $command[] = '--config-file=' . $foundConfig;
            // $command[] = '--quiet';
            $command[] = 'fix';

            exec(implode(' ', $command), $output, $exitCode);
            Console::stdout("done in " . round(microtime(true)-$dirStart, 1) . "s!" . PHP_EOL);
        }
        Console::stdout("done in " . round(microtime(true)-$phpcsStart, 1) . "s!" . PHP_EOL . PHP_EOL);
    }

    /**
     * [[@doctodo method_description:fixFile]].
     *
     * @return [[@doctodo return_type:fixFile]] [[@doctodo return_description:fixFile]]
     */
    public function fixFile($file)
    {
        $contents = preg_split("/\\r\\n|\\r|\\n/", file_get_contents($file));
        $changed = false;
        if ($this->fixFileSlashes($file, $contents)) {
            $changed = true;
        }
        // if ($this->fixDocBlockPlaceholder($file, $contents)) {
        //     $changed = true;
        // }
        if ($changed) {
            file_put_contents($file, implode("\n", $contents));
        }

        return $changed;
    }

    /**
     * [[@doctodo method_description:fixDocBlockPlaceholder]].
     *
     * @return [[@doctodo return_type:fixDocBlockPlaceholder]] [[@doctodo return_description:fixDocBlockPlaceholder]]
     */
    public function fixDocBlockPlaceholder($file, &$contents)
    {
        $changed = false;
        foreach ($contents as $lineNumber => $line) {
            $line = trim($line);
            if (substr($line, 0, 1) === '*' || substr($line, 0, 1) === '/') {
                if (strpos($line, '@doctodo write class description') !== false) {
                    unset($contents[$lineNumber]);
                    $changed = true;
                }
            }
        }

        return $changed;
    }

    /**
     * [[@doctodo method_description:fixFileSlashes]].
     *
     * @return [[@doctodo return_type:fixFileSlashes]] [[@doctodo return_description:fixFileSlashes]]
     */
    public function fixFileSlashes($file, &$contents)
    {
        $changed = false;
        foreach ($contents as $lineNumber => $line) {
            if (preg_match('/(psesd|infinite|cascade)\\\\\\\/', $line) === 1) {
                $fixedLine = preg_replace('/\\\\\\\/', '\\', $line);
                if ($fixedLine !== $line) {
                    $contents[$lineNumber] = $fixedLine;
                    $changed = true;
                }
            }
        }

        return $changed;
    }
}
