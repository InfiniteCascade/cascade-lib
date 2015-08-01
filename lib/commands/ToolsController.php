<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license
 */

namespace cascade\commands;

use canis\caching\Cacher;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * ToolsController runs various commands for Cascade.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ToolsController extends \canis\console\Controller
{
    /**
     * Flush the cache.
     *
     * @param string $category flush a particular category from cache [optional]
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
     * Fix the code formatting throughout Cascade and its related libraries.
     */
    public function actionFixProject()
    {
        $dirs = [
            Yii::getAlias('@cascade'),
            Yii::getAlias('@canis'),
            Yii::getAlias('@cascade/modules/core'),
            Yii::getAlias('@psesd/cascade'),
            Yii::getAlias('@canis/deferred'),
            Yii::getAlias('@canis/notification'),
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
     * Run a series of fixes on a file's code formatting.
     *
     * @param string $file file name of the file being checked
     *
     * @return bool if the file was changed
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
     * Removes the old @doctodo.
     *
     * @param string     $file     file name of the file being checked
     * @param array $contents array of the file contents
     *
     * @return bool if the file was changed
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
     * Fixes slashes inside namespace strings.
     *
     * @param string     $file     file name of the file being fixed
     * @param array $contents array of the file contents
     *
     * @return bool if the file changed
     */
    public function fixFileSlashes($file, &$contents)
    {
        $changed = false;
        foreach ($contents as $lineNumber => $line) {
            if (preg_match('/(psesd|canis|cascade)\\\\\\\/', $line) === 1) {
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
