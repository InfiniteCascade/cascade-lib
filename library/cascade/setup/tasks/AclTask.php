<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\setup\tasks;

use infinite\setup\Exception;

/**
 * AclTask [@doctodo write class description for AclTask]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class AclTask extends \infinite\setup\Task
{
    /**
    * @inheritdoc
    **/
    public function getTitle()
    {
        return 'ACL';
    }
    public function getBaseAcas()
    {
        return ['list', 'read', 'create', 'update', 'delete', 'archive'];
    }

    abstract public function getBaseRules();

    /**
    * @inheritdoc
    **/
    public function test()
    {
        foreach ($this->baseAcas as $aca) {
            $this->setup->app()->gk->getActionObjectByName($aca);
        }
        foreach ($this->baseRules as $rule) {
            if ($rule['task'] === 'allow') {
                $expected = true;
            } elseif ($rule['task'] === 'deny') {
                $expected = false;
            } else {
                $expected = null;
            }
            $controlled = $rule['controlled'];
            $accessing = $rule['accessing'];

            if (is_array($controlled)) {
                $model = $controlled['model'];
                $controlled = $model::find()->where($controlled['fields'])->one();
                if (!$controlled) {
                    return false;
                }
            }

            if (is_array($accessing)) {
                $model = $accessing['model'];
                $accessing = $model::find()->where($accessing['fields'])->one();
                if (!$accessing) {
                    return false;
                }
            }
            $result = $this->setup->app()->gk->can($rule['action'], $controlled, $accessing);
            if ($result !== $expected) {
                return false;
            }
        }

        return true;
    }
    /**
    * @inheritdoc
    **/
    public function run()
    {
        foreach ($this->baseRules as $rule) {
            $controlled = $rule['controlled'];
            $accessing = $rule['accessing'];

            if (is_array($controlled)) {
                $model = $controlled['model'];
                $controlled = $model::find()->disableAccessCheck()->where($controlled['fields'])->one();
                if (!$controlled) {
                    throw new Exception("Could not find controlled object: ". print_r($rule['controlled'], true));

                    return false;
                }
            }

            if (is_array($accessing)) {
                $model = $accessing['model'];
                $accessing = $model::find()->disableAccessCheck()->where($accessing['fields'])->one();
                if (!$accessing) {
                    throw new Exception("Could not find accessing object: ". print_r($rule['accessing'], true));

                    return false;
                }
            }

            if (!$this->setup->app()->gk->{$rule['task']}($rule['action'], $controlled, $accessing)) {
                    throw new Exception("Could not set up rule: ". print_r(['rule' => $rule], true));

                return false;
            }
        }

        return true;
    }
    /**
    * @inheritdoc
    **/
    public function getFields()
    {
        return false;
    }
}
