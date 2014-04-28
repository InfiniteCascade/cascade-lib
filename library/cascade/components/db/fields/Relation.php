<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use Yii;
use cascade\models\Relation as RelationModel;

/**
 * Relation [@doctodo write class description for Relation]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relation extends Base
{
    /**
     * @inheritdoc
     */
    public $formFieldClass = 'cascade\components\web\form\fields\Relation';
    /**
     * @inheritdoc
     */
    protected $_human = true;
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    protected $_moduleHandler;
    /**
     * @var __var_relationship_type__ __var_relationship_description__
     */
    public $relationship;
    /**
     * @var __var_modelRole_type__ __var_modelRole_description__
     */
    public $modelRole; // either parent or child
    /**
     * @var __var_baseModel_type__ __var_baseModel_description__
     */
    public $baseModel;
    /**
     * @var __var__moduleHandler_type__ __var__moduleHandler_description__
     */
    static $_moduleHandlers = [];

    protected $_value;


    public function __clone()
    {
        parent::__clone();
        $this->baseModel = clone $this->baseModel;
    }

    public function init()
    {
        parent::init();
        if (isset($this->relationship)) {
            $this->required = $this->relationship->required;
        }
    }

    /**
     * Get value
     * @return __return_getValue_type__ __return_getValue_description__
     */
    public function getValue()
    {
        if (is_null($this->_value)) {
            $relationTabularId = RelationModel::generateTabularId($this->field);
            $this->_value = false;
            $field = $this->field;
            $fieldParts = explode(':', $field);
            $primaryObject = $this->relationship->getRelatedObject($this->baseModel, $this->modelRole, $this->model);
            // if (isset($this->generator->models[$relationTabularId])) {
            //     $primaryObject->attributes = $this->generator->models[$relationTabularId]->attributes;
            // }
            if ($primaryObject) {
                $this->_value = $primaryObject;
            }
        }
        return $this->_value;
    }

    public function parseRelationTaxonomy($value)
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
        return $value;
    }

    public function resetModel()
    {
        $relationClass = Yii::$app->classes['Relation'];
        $this->_model = new $relationClass;
        $this->_model->tabularId = $this->field;
        $this->_model->_moduleHandler = $this->field;
        return $this->_model;
    }

    public function getModel()
    {
        if (is_null($this->_model)) {
            $relationTabularId = RelationModel::generateTabularId($this->field);
            $attributes = empty($this->attributes) ? [] : $this->attributes;
            $taxonomy = false;
            if (isset($attributes['taxonomy_id'])) {
                $taxonomy = $attributes['taxonomy_id'];
                unset($attributes['taxonomy_id']);
            }
            $relationOptions = ['where' => $attributes, 'taxonomy' => $taxonomy];
            $this->model = $this->relationship->getPrimaryRelation($this->baseModel, $this->modelRole, $relationOptions);
            if (empty($this->_model)) {
                $this->model = $this->baseModel->getRelationModel($this->field);
            }
            $this->_model->_moduleHandler = $this->field;
            if (empty($this->_model)) {
                \d("what");exit;
            }
        }
        return $this->_model;
    }

    /**
     * Get companion
     * @return __return_getCompanion_type__ __return_getCompanion_description__
     */
    public function getCompanion()
    {
        if ($this->modelRole === 'parent') {
            return $this->relationship->child;
        } else {
            return $this->relationship->parent;
        }
    }
    /**
     * Get module
     * @return __return_getModule_type__ __return_getModule_description__
     */
    public function getModule()
    {
        if ($this->modelRole === 'child') {
            return $this->relationship->child;
        } else {
            return $this->relationship->parent;
        }
    }

    /**
     * Get module handler
     * @return __return_getModuleHandler_type__ __return_getModuleHandler_description__
     */
    public function getModuleHandler()
    {
        if (is_null($this->_moduleHandler)) {
            $stem = $this->field;
            if (!isset(self::$_moduleHandlers[$stem])) { self::$_moduleHandlers[$stem] = []; }
            $n = count(self::$_moduleHandlers[$stem]);
            $this->_moduleHandler = $this->field .':_'. $n;
            self::$_moduleHandlers[$stem][] = $this->_moduleHandler;
        }

        return $this->_moduleHandler;
    }

    /**
    * @inheritdoc
     */
    public function hasFile()
    {
        return $this->companion->dummyModel->getBehavior('Storage') !== null;
    }

    /**
     * Get companion field
     * @return __return_getCompanionField_type__ __return_getCompanionField_description__
     */
    public function getCompanionField()
    {
        $fieldParts = explode(':', $this->field);
        if ($this->modelRole === 'parent') {
            return 'child:'.$fieldParts[1];
        } else {
            return 'parent:'.$fieldParts[1];
        }
    }

    /**
    * @inheritdoc
     */
    public function determineLocations()
    {
        if (!($this->modelRole === 'child' && !$this->relationship->isHasOne())
            &&	!($this->modelRole === 'parent')) {
            return [self::LOCATION_DETAILS];
        }

        return [self::LOCATION_HIDDEN];
    }

    public function getLabel()
    {
        return $this->relationship->getLabel($this->modelRole);
    }
}
