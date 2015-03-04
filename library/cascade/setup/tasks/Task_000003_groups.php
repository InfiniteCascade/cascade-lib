<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

use cascade\models\Group;
use cascade\models\Relation;

/**
 * Task_000003_groups [@doctodo write class description for Task_000003_groups].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Task_000003_groups extends \infinite\setup\Task
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Groups';
    }
    /**
     * Get base groups.
     */
    public function getBaseGroups()
    {
        return ['Top' => ['Users' => ['Administrators' => ['Super Administrators']], 'Public']];
    }

    /**
     * @inheritdoc
     */
    public function test()
    {
        return Group::find()->disableAccessCheck()->where(['system' => 'top'])->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $groups = $this->baseGroups;
        array_walk($groups, [$this, 'groupWalker']);

        return empty($this->errors);
    }

    /**
     *
     */
    public function groupWalker(&$item, $key, $mparent = null)
    {
        if (is_array($item)) {
            $parent  = Group::find()->disableAccessCheck()->where(['name' => $key])->one();
            if (empty($parent)) {
                $parent = new Group();
                //$parent->disableAcl();
                $parent->name = $key;
                $parent->system = preg_replace('/ /', '_', strtolower($parent->name));
                $parent->level = $this->getGroupLevel($key);

                if (!$parent->save()) {
                    $this->errors[] = "Failed to create group {$key}!";

                    return false;
                }
                if (!empty($mparent)) {
                    $r = new Relation();
                    $r->parent_object_id = $mparent;
                    $r->child_object_id = $parent->id;
                    $r->active = 1;
                    if (!$r->save()) {
                        $this->errors[] = "Failed to create group relationship {$key}!";

                        return false;
                    }
                }
            }
            $item = array_walk($item, [$this, 'groupWalker'], $parent->id);
        } else {
            $sitem = Group::find()->disableAccessCheck()->where(['name' => $item])->one();
            if (empty($sitem)) {
                $sitem = new Group();
                //$sitem->disableAcl();
                $sitem->name = $item;
                $sitem->system = preg_replace('/ /', '_', strtolower($sitem->name));
                $sitem->level = $this->getGroupLevel($item);

                if (!$sitem->save()) {
                    $this->errors[] = "Failed to create group {$item}!";

                    return false;
                }
                if (!empty($mparent)) {
                    $r = new Relation();
                    $r->parent_object_id = $mparent;
                    $r->child_object_id = $sitem->id;
                    $r->active = 1;
                    if (!$r->save()) {
                        $this->errors[] = "Failed to create group relationship {$key}!";

                        return false;
                    }
                }
            }
            $setup->registry['Group'][$item] = $sitem->id;
        }
    }

    /**
     * Get group level.
     */
    public function getGroupLevel($k)
    {
        switch ($k) {
        case 'Super Administrators':
            return 1001;
            break;
        case 'Administrators':
            return 1000;
            break;
        case 'Clients':
            return 1;
            break;
        case 'Top':
            return 0;
            break;
        default:
            return 100;
            break;
        }
    }
}
