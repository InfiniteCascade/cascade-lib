<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors\auditable;

use Yii;

/**
 * DeleteEvent [[@doctodo class_description:cascade\components\db\behaviors\auditable\DeleteEvent]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeleteEvent extends \canis\db\behaviors\auditable\DeleteEvent
{
    /**
     * @var [[@doctodo var_type:objectType]] [[@doctodo var_description:objectType]]
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

    /**
     * @inheritdoc
     */
    public function getVerb()
    {
        if (isset($this->directObject->objectType) && $this->directObject->objectType->getDeleteVerb($this->directObject) !== null) {
            return $this->directObject->objectType->getDeleteVerb($this->directObject);
        }

        return parent::getVerb();
    }

    /**
     * @inheritdoc
     */
    public function getStory()
    {
        $objectType = Yii::$app->collectors['types']->getOne($this->objectType);

        return '{{agent}} ' . $this->verb->past . ' ' . $objectType->object->title->getSingular(false) . ' [[' . $this->descriptor . ']]' . $this->indirectStory;
    }
}
