<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * ActiveAccess [[@doctodo class_description:cascade\components\db\behaviors\ActiveAccess]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveAccess extends \canis\db\behaviors\ActiveAccess
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
                && $relatedObject->can('associate:' . $this->owner->objectType->systemId);
    }

    /**
     * @inheritdoc
     */
    public function canUpdateAssociation($relatedObject)
    {
        return parent::canUpdateAssociation($relatedObject)
                && $relatedObject->can('associate:' . $this->owner->objectType->systemId);
    }
}
