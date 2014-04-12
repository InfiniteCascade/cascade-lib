<?php
namespace cascade\components\db\behaviors;

use Yii;
use infinite\helpers\ArrayHelper;

class ActiveAccess extends \infinite\db\behaviors\ActiveAccess
{
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

    public function canDeleteAssociation($relatedObject)
    {
        return parent::canDeleteAssociation($relatedObject)
                && $relatedObject->can('associate:'.$this->owner->objectType->systemId);
    }

    public function canUpdateAssociation($relatedObject)
    {
        return parent::canUpdateAssociation($relatedObject)
                && $relatedObject->can('associate:'.$this->owner->objectType->systemId);
    }
}
?>