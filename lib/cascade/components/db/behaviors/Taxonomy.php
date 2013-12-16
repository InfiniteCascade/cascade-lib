<?php
namespace cascade\components\db\behaviors;

use infinite\helpers\ArrayHelper;

class Taxonomy extends \infinite\db\behaviors\ActiveRecord
{
	public $taxonomyClass = 'cascade\\models\\Taxonomy';
	public $taxonomyTypeClass = 'cascade\\models\\TaxonomyType';
	public $viaModelClass = 'cascade\\models\\ObjectTaxonomy';
	public $relationKey = 'object_id';
	public $taxonomyKey = 'taxonomy_id';

	protected $_taxonomy_id;
	protected $_current_taxonomy_id;

    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave'
        ];
    }

    public function afterSave($event)
    {
    	if (!is_null($this->_taxonomy_id)) {
    		$pivotTableClass = $this->viaModelClass;
    		$current = $this->_currentTaxonomies();
    		foreach ($this->_taxonomy_id as $taxonomyId) {
    			if (in_array($taxonomyId, $current)) {
    				$deleteKey = array_search($taxonomyId, $current);
    				unset($current[$deleteKey]);
    				continue;
    			}
    			$base = [$this->taxonomyKey => $taxonomyId, $this->relationKey => $this->owner->primaryKey];
    			$taxonomy = new $pivotTableClass;
    			$taxonomy->attributes = $base;
    			if (!$taxonomy->save()) {
    				$event->isValid = false;
    			}
    		}
    		foreach ($current as $taxonomyId) {
                $baseFind = [$this->taxonomyKey => $taxonomyId, $this->relationKey => $this->owner->primaryKey];
                $taxonomy = $pivotTableClass::find()->where($baseFind)->one();

    			if ($taxonomy) {
    				if(!$taxonomy->delete()) {
    					$event->isValid = false;
    				}
    			}
    		}
    	}
    }

    public function setTaxonomy_id($value)
    {
        foreach ($value as $k => $v) {
            if (is_object($v)) {
                $value[$k] = $v->primaryKey;
            }
        }
    	$this->_taxonomy_id = $value;
    }

    public function _currentTaxonomies()
    {
    	if (is_null($this->_current_taxonomy_id)) {
    		$taxonomyClass = $this->viaModelClass;
    		$taxonomies = $taxonomyClass::find()->where([$this->relationKey => $this->owner->primaryKey])->all();
    		$this->_current_taxonomy_id = ArrayHelper::map($taxonomies, 'taxonomy_id', 'taxonomy_id');
    	}
    	return $this->_current_taxonomy_id;
    }

    public function getTaxonomy_id()
    {
    	if (is_null($this->_taxonomy_id)) {
    		return $this->_currentTaxonomies();
    	}
    	return $this->_taxonomy_id;
    }
}
?>