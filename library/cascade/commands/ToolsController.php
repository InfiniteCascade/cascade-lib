<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\commands;

use Yii;
use infinite\caching\Cacher;
use yii\helpers\FileHelper;
use yii\helpers\Console;

/**
 * ToolsController [@doctodo write class description for ToolsController]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ToolsController extends \infinite\console\Controller
{
    /**
     * __method_actionFlush_description__
     * @param __param_category_type__ $category __param_category_description__ [optional]
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

    public function actionFixSlashes()
    {
        $dirs = [Yii::getAlias('@cascade'), Yii::getAlias('@infinite'), Yii::getAlias('@cascade/modules/core'), Yii::getAlias('@cascade/modules/core'), Yii::getAlias('@infinite/deferred'), Yii::getAlias('@psesd/cascade')];
        foreach ($dirs as $dir) {
            $dirStart = microtime(true);
            $changed = 0;
            Console::stdout($dir ."...");
            $files = FileHelper::findFiles($dir, ['only' => ['*.php'], 'recursive' => true]);
            Console::stdout("found " .count($files)." files...");
            foreach ($files as $file) {
                if ($this->fixFileSlashes($file)) {
                    $changed++;
                }
            }
            Console::stdout("changed {$changed} files in ". round(microtime(true)-$dirStart,1)."s!".PHP_EOL);
        }
    }

    public function fixFileSlashes($file)
    {
        $contents = preg_split("/\\r\\n|\\r|\\n/", file_get_contents($file));
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
        if ($changed) {
            file_put_contents($file, implode("\n", $contents));
        }
        return $changed;
    }
}
