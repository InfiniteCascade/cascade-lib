<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * ActiveAccess [@doctodo write class description for ActiveAccess]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveAccess extends \infinite\db\behaviors\ActiveAccess
{
    /**
    * @inheritdoc
     */
    public function can($aca, $accessingObject = null, $relatedObject = false)
    {
        if (!is_object($aca)) {
            $aca = Yii::$app->gk->getActionObjectByName($aca);
        }
        if ($aca->name === 'archive') {
            if ($this->owner->getBehavior('Archivable') === null || !$this->owner->isArchivable()) {
                return false;
            }
        }

        return parent::can($aca, $accessingObject, $relatedObject);
    }

    /**
    * @inheritdoc
     */
    public function canDeleteAssociation($relatedObject)
    {
        return parent::canDeleteAssociation($relatedObject)
                && $relatedObject->can('associate:'.$this->owner->objectType->systemId);
    }

    /**
    * @inheritdoc
     */
    public function canUpdateAssociation($relatedObject)
    {
        return parent::canUpdateAssociation($relatedObject)
                && $relatedObject->can('associate:'.$this->owner->objectType->systemId);
    }
}
