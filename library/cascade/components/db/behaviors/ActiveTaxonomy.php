<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * Taxonomy [@doctodo write class description for Taxonomy].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveTaxonomy extends \infinite\db\behaviors\ActiveRecord
{
    /**
     * @var __var_viaModelClass_type__ __var_viaModelClass_description__
     */
    public $viaModelClass = 'ObjectTaxonomy';
    /**
     * @var __var_relationKey_type__ __var_relationKey_description__
     */
    public $relationKey = 'object_id';
    /**
     * @var __var_taxonomyKey_type__ __var_taxonomyKey_description__
     */
    public $taxonomyKey = 'taxonomy_id';

    /**
     * @var __var__taxonomy_id_type__ __var__taxonomy_id_description__
     */
    protected $_taxonomy_id;
    /**
     * @var __var__current_taxonomy_id_type__ __var__current_taxonomy_id_description__
     */
    protected $_current_taxonomy_id;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeAttributes()
    {
        return ['taxonomy_id'];
    }

    /**
     * __method_afterSave_description__.
     *
     * @param __param_event_type__ $event __param_event_description__
     */
    public function afterSave($event)
    {
        if (!is_null($this->_taxonomy_id)) {
            $pivotTableClass = Yii::$app->classes[$this->viaModelClass];
            $current = $this->_currentTaxonomies();
            foreach ($this->_taxonomy_id as $taxonomyId) {
                if (in_array($taxonomyId, $current)) {
                    $deleteKey = array_search($taxonomyId, $current);
                    unset($current[$deleteKey]);
                    continue;
                }
                $base = [$this->taxonomyKey => $taxonomyId, $this->relationKey => $this->owner->primaryKey];
                $taxonomy = new $pivotTableClass();
                $taxonomy->attributes = $base;
                if (!$taxonomy->save()) {
                    $event->isValid = false;
                }
            }
            foreach ($current as $taxonomyId) {
                $baseFind = [$this->taxonomyKey => $taxonomyId, $this->relationKey => $this->owner->primaryKey];
                $taxonomy = $pivotTableClass::find()->where($baseFind)->one();

                if ($taxonomy) {
                    if (!$taxonomy->delete()) {
                        $event->isValid = false;
                    }
                }
            }
        }
    }

    /**
     * Set taxonomy.
     *
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setTaxonomy_id($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $k => $v) {
            if (is_object($v)) {
                $value[$k] = $v->primaryKey;
            } elseif (is_array($v)) {
                unset($value[$k]);
                if (isset($v['systemId']) && isset($v['taxonomyType'])) {
                    $taxonomyType = Yii::$app->collectors['taxonomies']->getOne($v['taxonomyType']);
                    if (isset($taxonomyType) && ($taxonomy = $taxonomyType->getTaxonomy($v['systemId']))) {
                        $value[$k] = $taxonomy->primaryKey;
                    }
                }
            }
        }
        $this->_taxonomy_id = $value;
    }

    /**
     * __method__currentTaxonomies_description__.
     *
     * @return __return__currentTaxonomies_type__ __return__currentTaxonomies_description__
     */
    public function _currentTaxonomies()
    {
        if (is_null($this->_current_taxonomy_id)) {
            $taxonomyClass = Yii::$app->classes[$this->viaModelClass];
            $taxonomies = $taxonomyClass::find()->where([$this->relationKey => $this->owner->primaryKey])->select('taxonomy_id')->column();
            $this->_current_taxonomy_id = array_combine($taxonomies, $taxonomies);
        }

        return $this->_current_taxonomy_id;
    }

    /**
     * Get taxonomy.
     *
     * @return __return_getTaxonomy_id_type__ __return_getTaxonomy_id_description__
     */
    public function getTaxonomy_id()
    {
        if (is_null($this->_taxonomy_id)) {
            return $this->_currentTaxonomies();
        }

        return $this->_taxonomy_id;
    }
}
