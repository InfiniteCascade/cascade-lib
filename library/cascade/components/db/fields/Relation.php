<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use cascade\models\Relation as RelationModel;
use Yii;

/**
 * Relation [[@doctodo class_description:cascade\components\db\fields\Relation]].
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
     * @var [[@doctodo var_type:_moduleHandler]] [[@doctodo var_description:_moduleHandler]]
     */
    protected $_moduleHandler;
    /**
     * @var [[@doctodo var_type:relationship]] [[@doctodo var_description:relationship]]
     */
    public $relationship;
    /**
     * @var [[@doctodo var_type:modelRole]] [[@doctodo var_description:modelRole]]
     */
    public $modelRole; // either parent or child
    /**
     * @var [[@doctodo var_type:baseModel]] [[@doctodo var_description:baseModel]]
     */
    public $baseModel;
    /*
     */
    /**
     * @var [[@doctodo var_type:_moduleHandlers]] [[@doctodo var_description:_moduleHandlers]]
     */
    public static $_moduleHandlers = [];

    /**
     * @var [[@doctodo var_type:_value]] [[@doctodo var_description:_value]]
     */
    protected $_value;

    /**
     * @inheritdoc
     */
    public function __clone()
    {
        parent::__clone();
        $this->baseModel = clone $this->baseModel;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (isset($this->relationship)) {
            $this->required = $this->relationship->required;
        }
    }

    /**
     * Get value.
     *
     * @param boolean $createOnEmpty [[@doctodo param_description:createOnEmpty]] [optional]
     *
     * @return [[@doctodo return_type:getValue]] [[@doctodo return_description:getValue]]
     */
    public function getValue($createOnEmpty = true)
    {
        if (is_null($this->_value)) {
            $models = $this->baseModel->collectModels();
            $relationTabularId = RelationModel::generateTabularId($this->field);
            $this->_value = false;
            $field = $this->field;
            $fieldParts = explode(':', $field);
            $primaryObject = $this->relationship->getRelatedObject($this->baseModel, $this->modelRole, $this->model);

            if (isset($models[$relationTabularId])) {
                $this->_value = $models[$relationTabularId];
            } elseif ($primaryObject) {
                $this->_value = $primaryObject;
            } elseif ($createOnEmpty) {
                $modelClass = $this->relationship->companionRoleType($this->modelRole)->primaryModel;
                $this->_value = new $modelClass();
                $this->_value->tabularId = $this->field;
                $this->_value->_moduleHandler = $this->field;
            }
            $this->_value->setParentModel($this->baseModel);
          //  exit;
        }

        return $this->_value;
    }

    /**
     * [[@doctodo method_description:parseRelationTaxonomy]].
     *
     * @return [[@doctodo return_type:parseRelationTaxonomy]] [[@doctodo return_description:parseRelationTaxonomy]]
     */
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

    /**
     * @inheritdoc
     */
    public function resetModel()
    {
        $relationClass = Yii::$app->classes['Relation'];
        $this->_model = new $relationClass();
        $this->_model->tabularId = $this->field;
        $this->_model->_moduleHandler = $this->field;

        return $this->_model;
    }

    /**
     * @inheritdoc
     */
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
                $this->model = $this->baseModel->getObjectRelationModel($this->field);
            }
            $this->_model->_moduleHandler = $this->field;
            if (empty($this->_model)) {
                \d("what");
                exit;
            }
        }

        return $this->_model;
    }

    /**
     * Get companion.
     *
     * @return [[@doctodo return_type:getCompanion]] [[@doctodo return_description:getCompanion]]
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
     * Get module.
     *
     * @return [[@doctodo return_type:getModule]] [[@doctodo return_description:getModule]]
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
     * Get module handler.
     *
     * @return [[@doctodo return_type:getModuleHandler]] [[@doctodo return_description:getModuleHandler]]
     */
    public function getModuleHandler()
    {
        if (is_null($this->_moduleHandler)) {
            $stem = $this->field;
            if (!isset(self::$_moduleHandlers[$stem])) {
                self::$_moduleHandlers[$stem] = [];
            }
            $n = count(self::$_moduleHandlers[$stem]);
            $this->_moduleHandler = $this->field . ':_' . $n;
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
     * Get companion field.
     *
     * @return [[@doctodo return_type:getCompanionField]] [[@doctodo return_description:getCompanionField]]
     */
    public function getCompanionField()
    {
        $fieldParts = explode(':', $this->field);
        if ($this->modelRole === 'parent') {
            return 'child:' . $fieldParts[1];
        } else {
            return 'parent:' . $fieldParts[1];
        }
    }

    /**
     * @inheritdoc
     */
    public function determineLocations()
    {
        if (!($this->modelRole === 'child' && !$this->relationship->isHasOne())
            &&    !($this->modelRole === 'parent')) {
            return [self::LOCATION_DETAILS];
        }

        return [self::LOCATION_HIDDEN];
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        if (isset($this->baseModel)) {
            $labels = $this->baseModel->attributeLabels();
            if (isset($labels[$this->field])) {
                return ($labels[$this->field]);
            }
        }

        return $this->relationship->getLabel($this->modelRole);
    }

    /**
     * @inheritdoc
     */
    public function getFilterSettings()
    {
        return false;
    }
}
