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
 * ActiveTaxonomy [[@doctodo class_description:cascade\components\db\behaviors\ActiveTaxonomy]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveTaxonomy extends \infinite\db\behaviors\ActiveRecord
{
    /**
     * @var [[@doctodo var_type:viaModelClass]] [[@doctodo var_description:viaModelClass]]
     */
    public $viaModelClass = 'ObjectTaxonomy';
    /**
     * @var [[@doctodo var_type:relationKey]] [[@doctodo var_description:relationKey]]
     */
    public $relationKey = 'object_id';
    /**
     * @var [[@doctodo var_type:taxonomyKey]] [[@doctodo var_description:taxonomyKey]]
     */
    public $taxonomyKey = 'taxonomy_id';

    /**
     * @var [[@doctodo var_type:_taxonomy_id]] [[@doctodo var_description:_taxonomy_id]]
     */
    protected $_taxonomy_id;
    /**
     * @var [[@doctodo var_type:_current_taxonomy_id]] [[@doctodo var_description:_current_taxonomy_id]]
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
     * [[@doctodo method_description:afterSave]].
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
     * [[@doctodo method_description:_currentTaxonomies]].
     *
     * @return [[@doctodo return_type:_currentTaxonomies]] [[@doctodo return_description:_currentTaxonomies]]
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
     * @return [[@doctodo return_type:getTaxonomy_id]] [[@doctodo return_description:getTaxonomy_id]]
     */
    public function getTaxonomy_id()
    {
        if (is_null($this->_taxonomy_id)) {
            return $this->_currentTaxonomies();
        }

        return $this->_taxonomy_id;
    }
}
