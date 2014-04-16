<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\security;

use cascade\components\types\Module as TypeModule;
use cascade\components\types\RelationshipEvent;
use infinite\caching\Cacher;

class AuthorityBehavior extends \infinite\security\AuthorityBehavior
{
    public function events()
    {
        return array_merge(parent::events(), [
            TypeModule::EVENT_RELATION_CHANGE => [$this, 'handleRelationChange']
        ]);
    }

    public function handleRelationChange(RelationshipEvent $event)
    {
        if (get_class($event->parentObject) === $this->owner->primaryModel) {
            Cacher::invalidateGroup('aros');
        }
    }
}
