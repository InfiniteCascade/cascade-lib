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
 * Relation [@doctodo write class description for Relation].
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
     */
    protected $_moduleHandler;
    /**
     */
    public $relationship;
    /**
     */
    public $modelRole; // either parent or child
    /**
     */
    public $baseModel;
    /*
     */
    public static $_moduleHandlers = [];

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
     * Get value.
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
        $this->_model = new $relationClass();
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

    public function getFilterSettings()
    {
        return false;
    }
}
