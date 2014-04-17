<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\commands;

use infinite\caching\Cacher;

/**
 * ToolsController [@doctodo write class description for ToolsController]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class ToolsController extends \infinite\console\Controller
{
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
}
