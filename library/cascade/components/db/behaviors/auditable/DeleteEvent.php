<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

use Yii;

/**
 * DeleteEvent [@doctodo write class description for DeleteEvent].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeleteEvent extends \infinite\db\behaviors\auditable\DeleteEvent
{
    /**
     */
    public $objectType;

    /**
     * @inheritdoc
     */
    public function setDirectObject($object)
    {
        parent::setDirectObject($object);
        if ($object->objectType) {
            $this->objectType = $object->objectType->systemId;
        }
    }

    public function getVerb()
    {
        if (isset($this->directObject->objectType) && $this->directObject->objectType->getDeleteVerb($this->directObject) !== null) {
            return $this->directObject->objectType->getDeleteVerb($this->directObject);
        }

        return parent::getVerb();
    }

    public function getStory()
    {
        $objectType = Yii::$app->collectors['types']->getOne($this->objectType);

        return '{{agent}} ' . $this->verb->past . ' ' . $objectType->object->title->getSingular(false) . ' [[' . $this->descriptor . ']]' . $this->indirectStory;
    }
}
