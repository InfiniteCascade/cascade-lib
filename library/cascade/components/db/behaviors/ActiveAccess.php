<?php
namespace cascade\components\db\behaviors;

use infinite\helpers\ArrayHelper;

class ActiveAccess extends \infinite\db\behaviors\ActiveAccess
{
    public function canDeleteAssociation($relatedObject)
    {
        return parent::canDeleteAssociation($relatedObject)
                && $relatedObject->can('associate:'.$this->owner->objectType->systemId);
    }
}
?>