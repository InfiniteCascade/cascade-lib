<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\types;

use cascade\components\db\behaviors\SearchTerm;
use cascade\components\db\fields\Base as BaseField;
use cascade\components\web\form\Segment as FormSegment;
use cascade\models\Relation;
use teal\caching\Cacher;
use teal\helpers\ArrayHelper;
use teal\helpers\Html;
use Yii;
use yii\helpers\Inflector;

trait ActiveRecordTrait
{
    use SearchTerm;

    public $baseFieldClass = 'cascade\components\db\fields\Base';
    public $artificialFieldClass = 'cascade\components\db\fields\Artificial';
    public $modelFieldClass = 'cascade\components\db\fields\Model';
    public $relationFieldClass = 'cascade\components\db\fields\Relation';
    public $relationTaxonomyFieldClass = 'cascade\components\db\fields\RelationTaxonomy';
    public $taxonomyFieldClass = 'cascade\components\db\fields\Taxonomy';
    public $formSegmentClass = 'cascade\components\web\form\Segment';
    public $columnSchemaClass = 'yii\\db\\ColumnSchema';

    public $foreignWeight = .7;

    public $defaultCustomColumnSchema = [
        'allowNull' => false,
        'type' => 'string',
        'phpType' => 'string',
        'dbType' => 'string',
        'isPrimaryKey' => false,
    ];

    protected $_fields;
    protected $_parentModel;
    protected $_defaultOrder;

    public function behaviors()
    {
        return [
            'Registry' => [
                'class' => 'teal\db\behaviors\Registry',
            ],
            'Relatable' => [
                'class' => 'cascade\components\db\behaviors\Relatable',
            ],
            'Taxonomy' => [
                'class' => 'cascade\components\db\behaviors\ActiveTaxonomy',
            ],
            'ActiveAccess' => [
                'class' => 'cascade\components\db\behaviors\ActiveAccess',
            ],
            'Roleable' => [
                'class' => 'cascade\components\db\behaviors\Roleable',
            ],
            'Ownable' => [
                'class' => 'cascade\components\db\behaviors\Ownable',
            ],
            'Familiarity' => [
                'class' => 'cascade\components\db\behaviors\Familiarity',
            ],
            'Auditable' => [
                'class' => 'cascade\components\db\behaviors\auditable\Auditable',
            ],
            'RelatedObjects' => [
                'class' => 'cascade\components\db\behaviors\RelatedObjects',
            ],
        ];
    }

    public static function queryBehaviors()
    {
        return array_merge(parent::queryBehaviors(),
            [
                'Taxonomy' => [
                    'class' => 'cascade\components\db\behaviors\QueryTaxonomy',
                ],
            ]
        );
    }

    public function hasIcon()
    {
        return false;
    }

    public function getIcon()
    {
        if (!$this->objectType) {
            return;
        }

        return [
            'class' => $this->objectType->icon,
        ];
    }

    public function getHumanType()
    {
        if (isset($this->objectType) && $this->objectType->title) {
            return $this->objectType->title->singular;
        }

        return;
    }

    public function formName()
    {
        $parentFormName = parent::formName();
        if (isset($this->_parentModel)) {
            $parentModelClass = get_class($this->_parentModel);

            return $this->_parentModel->formName() . $this->_parentModel->tabularPrefix . '[relatedObjects][' . $parentFormName . ']';
        }

        return $parentFormName;
    }

    public function setParentModel($parentModel)
    {
        $this->_parentModel = $parentModel;
    }

    public function isForeignField($field)
    {
        return parent::isForeignField($field) && strpos($field, ':') !== false;
    }

    public static function searchForeign($term, $field, $params = [])
    {
        $defaultParams = ['foreignWeight' => .8, 'foreignLimit' => 5, 'ignore' => []];
        $params = array_merge($defaultParams, $params);
        $modelClass = get_called_class();
        $model = new $modelClass();

        $package = [];
        $parts = explode(':', $field);
        if (count($parts) < 2) {
            return $package;
        }

        $relationshipType = $parts[0];
        if (!in_array($relationshipType, ['child', 'children', 'descendants', 'parent', 'parents', 'ancestors'])) {
            return;
        }

        $searchFields = static::searchFields();
        $companionName = $parts[1];
        $fieldName = isset($parts[2]) ? $parts[2] : 'descriptor';
        $myTypeItem = $model->objectTypeItem;
        $weightField = $parts[0] . 'SearchWeight';
        $companionTypeItem = Yii::$app->collectors['types']->getOne($companionName);
        if (!$companionTypeItem || !$myTypeItem || !($companionType = $companionTypeItem->object) || !($myType = $myTypeItem->object)) {
            return;
        }

        if ($companionType->{$weightField} && $companionType->{$weightField} !== true) {
            $searchWeight = (float) $companionType->{$weightField};
        } else {
            $searchWeight = $params['foreignWeight'];
        }
        if (in_array($relationshipType, ['child', 'children', 'descendants'])) {
            // I'm the parent
            $relationship = Relationship::has($myTypeItem, $companionTypeItem) ? Relationship::getOne($myTypeItem, $companionTypeItem) : false;
            $seek = 'queryParentObjects';
        } else {
            $relationship = Relationship::has($companionTypeItem, $myTypeItem) ? Relationship::getOne($companionTypeItem, $myTypeItem) : false;
            $seek = 'queryChildObjects';
        }
        if (!$relationship) {
            return $package;
        }
        $newParams = [];
        $newParams['skipForeign'] = true;
        $newParams['limit'] = $params['foreignLimit'];
        $newParams['action'] = isset($params['action']) ? $params['action'] : null;
        $results = $companionType->search($term, $newParams);
        foreach ($results as $result) {
            if (empty($result->object)) {
                continue;
            }
            foreach ($result->object->$seek($myType->primaryModel)->setAction($newParams['action'])->all() as $object) {
                $foreignResult = self::createSearchResult($object, $searchFields, $result->score * $searchWeight);
                if (!$foreignResult) {
                    continue;
                }
                $package[$object->primaryKey] = $foreignResult;
                $package[$object->primaryKey]->mergeTerms([$result->object->descriptor]);
                $package[$object->primaryKey]->addSubdescriptorValue($result->object->descriptor);
            }
        }

        return $package;
    }

    public function isForeignObjectInContext($object, $context = null)
    {
        if (empty($context) || !is_array($context)) {
            return false;
        }
        if (isset($context['object'])) {
            $objectTest = $context['object'];
            if (is_object($objectTest)) {
                $objectTest = $objectTest->primaryKey;
            }
            if ($objectTest === $object->primaryKey) {
                return true;
            }
        }

        return false;
    }

    public function getLocalFieldValue($field, $options = [], $context = null, $formatted = true)
    {
        if (isset($this->{$field})) {
            if ($formatted) {
                $field = $this->getField($field);
                if (!$field) {
                    return;
                }

                return $field->formattedValue;
            }

            return $this->{$field};
        }

        return;
    }

    public function getForeignField($field, $options = [], $context = null)
    {
        $origFieldName = $field;
        $relationOptions = isset($options['relationOptions']) ? $options['relationOptions'] : [];
        $objectOptions = isset($options['objectOptions']) ? $options['objectOptions'] : [];
        $parts = explode(':', $field);
        $relationshipType = $parts[0];
        if (!in_array($relationshipType, ['child', 'children', 'descendants', 'parent', 'parents', 'ancestors'])) {
            if ($field === 'parent:Account') {
                \d(['hmmm']);
                exit;
            }

            return;
        }

        $myTypeItem = $this->objectTypeItem;
        $companionName = $parts[1];
        if (!is_array($context)) {
            $context = [];
        }
        if (!isset($context['relation'])) {
            $context['relation'] = [];
        }
        $fieldName = 'descriptor';
        if (!empty($parts[2])) {
            $fieldName = $parts[2];
            $parts[2] = '';
        }
        $fields = $this->getFields();
        $fieldCheck = implode(':', $parts);

        if (in_array($fieldCheck, $context['relation']) && empty($options['relationOptions']['taxonomy'])) {
            return;
        }
        if ($companionName === '_') {
            if (in_array($parts[0], ['parent', 'parents', 'ancestors'])) {
                $loopRelations = $myTypeItem->parents;
                $loopRelations = ArrayHelper::getColumn($loopRelations, 'parent');
            } else {
                $loopRelations = $myTypeItem->children;
                $loopRelations = ArrayHelper::getColumn($loopRelations, 'child');
            }
            ArrayHelper::multisort($loopRelations, 'priority', SORT_ASC);
            foreach ($loopRelations as $relatedType) {
                $fieldName = $relationshipType . ':' . $relatedType->systemId;
                if (isset($parts[2])) {
                    $fieldName .= ':' . $parts[2];
                }
                $fieldValue = $this->getForeignField($fieldName, $options, $context);
                if (!empty($fieldValue)) {
                    return $fieldValue;
                }
            }

            return;
        }
        $companionTypeItem = Yii::$app->collectors['types']->getOne($companionName);
        if (!$companionTypeItem || !$myTypeItem || !($companionType = $companionTypeItem->object) || !($myType = $myTypeItem->object)) {
            return;
        }
        if (in_array($relationshipType, ['child', 'children', 'descendants'])) {
            // I'm the parent
            $relationship = Relationship::has($myTypeItem, $companionTypeItem) ? Relationship::getOne($myTypeItem, $companionTypeItem) : false;
        } else {
            $relationship = Relationship::has($companionTypeItem, $myTypeItem) ? Relationship::getOne($companionTypeItem, $myTypeItem) : false;
        }
        if (!$relationship) {
            return;
        }
        $cacheKey = [__FUNCTION__, $this->primaryKey, $fieldCheck, $relationshipType, $relationOptions, $objectOptions];
        $result = false; // Cacher::get($cacheKey);
        if ($result === false) {
            if (isset($fields[$fieldCheck])) {
                $field = $fields[$fieldCheck];
                if (isset($field->attributes['taxonomy_id'])) {
                    $relationOptions['taxonomy'] = $field->attributes['taxonomy_id'];
                }
                $relationOptions['where'] = !empty($field->attributes) ? $field->attributes : null;
                unset($relationOptions['where']['taxonomy_id']);
                if (empty($relationOptions['where'])) {
                    unset($relationOptions['where']);
                }
            }
            $cacheDependencies = [];
            $cacheDependencies[] = $this->getRelationCacheDependency($this->primaryKey);
            $result = $this->{$relationshipType}($companionType->primaryModel, $relationOptions, $objectOptions);
            if (empty($result)) {
                $result = null;
            } else {
                $cacheDependencies[] = $result->getObjectCacheDependency();
            }
            Cacher::set($cacheKey, $result, 0, Cacher::chainedDependency($cacheDependencies));
        }
        if (empty($result)) {
            return;
        }
        if (is_array($result)) {
            $fields = [];
            foreach ($result as $object) {
                if ($this->isForeignObjectInContext($object, $context)) {
                    continue;
                }
                $field = $object->getField($fieldName);
                if (empty($field)) {
                    continue;
                }
                $companionType->loadFieldLink($field, $object);
                $fields[] = $field;
            }
            if (empty($fields)) {
                return;
            }

            return $fields;
        } else {
            if ($this->isForeignObjectInContext($result, $context)) {
                return;
            }
            $field = $result->getField($fieldName);
            if (empty($field)) {
                return;
            }
            $companionType->loadFieldLink($field, $result);

            return $field;
        }

        return;
    }

    public function getForeignFieldOld($field, $options = [], $context = null)
    {
        $relationOptions = isset($options['relationOptions']) ? $options['relationOptions'] : [];
        $objectOptions = isset($options['objectOptions']) ? $options['objectOptions'] : [];
        $parts = explode(':', $field);
        if (!in_array(count($parts), [2, 3])) {
            return;
        }
        $relationshipType = $parts[0];
        if (!in_array($relationshipType, ['child', 'children', 'descendants', 'parent', 'parents', 'ancestors'])) {
            return;
        }
        $myTypeItem = $this->objectTypeItem;
        $companionName = $parts[1];
        if (!is_array($context)) {
            $context = [];
        }
        if (!isset($context['relation'])) {
            $context['relation'] = [];
        }
        $baseName = [];
        if (in_array($parts[0], ['parent', 'parents', 'ancestors'])) {
            $baseName[] = 'parent';
        } else {
            $baseName[] = 'child';
        }
        $baseName[] = $parts[1];
        if (in_array(implode(':', $baseName), $context['relation'])) {
            return;
        }
        if ($companionName === '_') {
            if (in_array($parts[0], ['parent', 'parents', 'ancestors'])) {
                $loopRelations = $myTypeItem->parents;
                $loopRelations = ArrayHelper::getColumn($loopRelations, 'parent');
            } else {
                $loopRelations = $myTypeItem->children;
                $loopRelations = ArrayHelper::getColumn($loopRelations, 'child');
            }
            ArrayHelper::multisort($loopRelations, 'priority', SORT_ASC);
            foreach ($loopRelations as $relatedType) {
                $fieldName = $relationshipType . ':' . $relatedType->systemId;
                if (isset($parts[2])) {
                    $fieldName .= ':' . $parts[2];
                }
                $fieldValue = $this->getForeignField($fieldName, $options, $context);
                if (!empty($fieldValue)) {
                    return $fieldValue;
                }
            }

            return;
        }
        $fieldName = isset($parts[2]) ? $parts[2] : 'descriptor';
        $companionTypeItem = Yii::$app->collectors['types']->getOne($companionName);
        if (!$companionTypeItem || !$myTypeItem || !($companionType = $companionTypeItem->object) || !($myType = $myTypeItem->object)) {
            return;
        }
        if (in_array($relationshipType, ['child', 'children', 'descendants'])) {
            // I'm the parent
            $relationship = Relationship::has($myTypeItem, $companionTypeItem) ? Relationship::getOne($myTypeItem, $companionTypeItem) : false;
        } else {
            $relationship = Relationship::has($companionTypeItem, $myTypeItem) ? Relationship::getOne($companionTypeItem, $myTypeItem) : false;
        }
        if (!$relationship) {
            return;
        }
        $cacheKey = [__FUNCTION__, $this->primaryKey, $relationshipType, $relationOptions, $objectOptions];
        $result = Cacher::get($cacheKey);
        if ($result === false) {
            $cacheDependencies = [];
            $cacheDependencies[] = $this->getRelationCacheDependency($this->primaryKey);
            // @todo remove this next line
            // unset($relationOptions['taxonomy']);
            //\d($relationOptions['taxonomy']);
            $result = $this->{$relationshipType}($companionType->primaryModel, $relationOptions, $objectOptions);
            if (empty($result)) {
                $result = null;
            } else {
                $cacheDependencies[] = $result->getObjectCacheDependency();
            }
            Cacher::set($cacheKey, $result, 0, Cacher::chainedDependency($cacheDependencies));
        }
        if (empty($result)) {
            return;
        }
        if (is_array($result)) {
            $fields = [];
            foreach ($result as $object) {
                if ($this->isForeignObjectInContext($object, $context)) {
                    continue;
                }
                $field = $object->getField($fieldName);
                if (empty($field)) {
                    continue;
                }
                $companionType->loadFieldLink($field, $object);
                $fields[] = $field;
            }
            if (empty($fields)) {
                return;
            }

            return $fields;
        } else {
            if ($this->isForeignObjectInContext($result, $context)) {
                return;
            }
            $field = $result->getField($fieldName);
            if (empty($field)) {
                continue;
            }
            $companionType->loadFieldLink($field, $result);

            return $field;
        }

        return;
    }

    public function getObjectCacheDependency()
    {
        return Cacher::groupDependency(['Object', $this->primaryKey], 'object');
    }

    public function getForeignFieldValue($fieldName, $options = [], $context = null, $formatted = true)
    {
        $field = $this->getForeignField($fieldName, $options, $context);
        if (!empty($field)) {
            if (is_array($field)) {
                $rich = [];
                $plain = [];
                foreach ($field as $object) {
                    $valuePackage = $object->valuePackage;
                    if (!empty($valuePackage) && !empty($valuePackage['rich']) && !empty($valuePackage['plain'])) {
                        $rich[] = $valuePackage['rich'];
                        $plain[] = $valuePackage['plain'];
                    }
                }

                return ['rich' => implode(', ', $rich), 'plain' => implode(', ', $plain)];
            } else {
                return $field->valuePackage;
            }
        }

        return;
    }

    public static function searchFields()
    {
        $modelClass = get_called_class();
        $model = new $modelClass();

        $fields = [];
        if (!is_null($model->descriptorField)) {
            if (is_array($model->descriptorField)) {
                $fields[] = $model->descriptorField;
            } else {
                $fields[] = [$model->descriptorField];
            }
        }
        $attributes = $model->attributes();
        foreach ($fields as $key => $fieldList) {
            if (!is_array($fieldList)) {
                \d($fields);
                exit;
            }
            foreach ($fieldList as $fieldKey => $field) {
                if (!in_array($field, $attributes)) {
                    unset($fields[$key][$fieldKey]);
                }
                if (empty($fields[$key])) {
                    unset($fields[$key]);
                }
            }
        }
        // $fields = array_intersect($fields, );
        if (($moduleItem = $model->getObjectTypeItem())) {
            foreach ($moduleItem->children as $key => $relationship) {
                if ($relationship->child->childSearchWeight) {
                    $fields[] = 'child:' . $key;
                }
            }
            foreach ($moduleItem->parents as $key => $relationship) {
                if ($relationship->parent->parentSearchWeight) {
                    $fields[] = 'parent:' . $key;
                }
            }
        }

        return $fields;
    }

    public static function prepareObjectTerms($object, $fields)
    {
        $terms = [];
        foreach ($fields as $fieldList) {
            if (!is_array($fieldList)) {
                $fieldList = [$fieldList];
            }
            foreach ($fieldList as $field) {
                if (strpos(':', $field) === false) {
                    continue;
                }
                if (!empty($object->{$field})) {
                    $terms[] = $object->{$field};
                }
            }
        }

        return $terms;
    }

    public static function parseSearchFields($fields)
    {
        $result = ['local' => [], 'foreign' => []];
        foreach ($fields as $fieldKey => $fieldList) {
            if (!is_array($fieldList)) {
                $fieldList = [$fieldList];
            }
            foreach ($fieldList as $field) {
                if (strpos($field, ':') === false) {
                    $destination = 'local';
                } else {
                    $destination = 'foreign';
                }
                if (!isset($result[$destination][$fieldKey])) {
                    $result[$destination][$fieldKey] = [];
                }
                $result[$destination][$fieldKey][] = $field;
            }
        }
        $result['local'] = array_values($result['local']);
        $result['foreign'] = array_values($result['foreign']);

        return $result;
    }

    public function isEmptyObject()
    {
        $default = $this->defaultValues;
        foreach ($this->attributes as $key => $value) {
            if ($this->isAttributeChanged($key)
                && !empty($value)
                && (!isset($default[$key]) || $value == $default[$key])
            ) {
                return false;
            }
        }

        return true;
    }

    public function getDefaultValues()
    {
        return [];
    }

    public function loadDefaultValues($skipIfSet = true)
    {
        $fields = $this->getFields();
        $defaultValues = $this->getDefaultValues();
        foreach ($defaultValues as $k => $v) {
            if ($this->isForeignField($k)) {
                if (isset($fields[$k])) {
                    $fieldParts = explode(':', $k);
                    if (in_array($fieldParts[0], ['child', 'children', 'descendants'])) {
                        $fields[$k]->model->child_object_id = $v;
                    } else {
                        $fields[$k]->model->parent_object_id = $v;
                    }
                }
            } else {
                if ($v !== null && (!$skipIfSet || $this->{$k} === null)) {
                    $this->{$k} = $v;
                }
            }
        }
        parent::loadDefaultValues($skipIfSet);
    }

    public function getPackage($urlAction = 'view')
    {
        $p = parent::getPackage($urlAction);
        $p['type'] = $this->objectTypeItem->systemId;
        if ($this->hasIcon()) {
            $p['icon'] = $this->getIcon();
        }

        return $p;
    }

    public function getUrl($action = 'view', $base = [], $pathLink = true)
    {
        $url = ['/object/' . $action, 'id' => $this->primaryKey];
        $url = array_merge($url, $base);
        if ($action === 'view'
                && $pathLink === true
                && isset(Yii::$app->request->object)
                && Yii::$app->request->object->primaryKey !== $this->primaryKey) {
            $url['p'] = Yii::$app->request->object->primaryKey;
        }

        return $url;
    }

    public function getViewLink()
    {
        if ($this->can('read')) {
            return Html::a($this->descriptor, $this->getUrl('view'));
        } else {
            return $this->descriptor;
        }
    }

    public function allowRogue(Relation $relation = null)
    {
        $relationship = false;
        if (is_null($relation)) {
            $relation = false;
        } else {
            $relationship = $relation->relationship;
        }

        if ($relationship && $relationship->isHasOne()) {
            return false;
        }
        if ($relation && isset($relation->childObject) && isset($relation->childObject->objectType) && $relation->childObject->objectType->uniparental) {
            return false;
        }
        if ($this->objectType->hasDashboard) {
            return true;
        }
        if ($this->getBehavior('Relatable') !== null && count($this->parentIds) > 1) {
            return true;
        }

        return false;
    }

    /**
     * @param unknown $name
     * @param unknown $settings (optional)
     *
     * @return unknown
     */
    public function form($settings = [])
    {
        Yii::beginProfile(__CLASS__ . ':' . __FUNCTION__);
        $settings['class'] = $this->formSegmentClass;
        $settings['model'] = $this;
        if (!isset($settings['settings'])) {
            $settings['settings'] = [];
        }
        $form = Yii::createObject($settings);
        // $form = new FormSegment($this, $name, $settings);
        Yii::endProfile(__CLASS__ . ':' . __FUNCTION__);

        return $form;
    }

    /**
     * @return unknown
     */
    public function additionalFields()
    {
        return [
            '_moduleHandler' => [],
        ];
    }

    /**
     * @return unknown
     */
    public function getAdditionalAttributes()
    {
        $add = [];
        $af = $this->additionalFields();
        foreach (array_keys($af) as $field) {
            if (isset($this->{$field})) {
                $add[$field] = $this->{$field};
            }
        }

        return $add;
    }

    public function getRequiredFields($owner = null)
    {
        $fields = $this->getFields($owner);
        $required = [];
        foreach ($fields as $key => $field) {
            if (!$field->human) {
                continue;
            }
            if ($field->required) {
                $required[$key] = $field;
            }
        }

        return $required;
    }

    public function getHeaderDetails()
    {
        return [];
    }

    public function getFieldLocation($location, $owner = null, $allFields = null)
    {
        if (is_null($allFields)) {
            $allFields = $this->getFields($owner);
        }
        $fields = [];
        foreach ($allFields as $key => $field) {
            if (in_array($location, $field->locations)) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function getDetailFields($owner = null, $fields = null)
    {
        return $this->getFieldLocation(BaseField::LOCATION_DETAILS, $owner, $fields);
    }

    public function getHiddenFields($owner = null, $fields = null)
    {
        return $this->getFieldLocation(BaseField::LOCATION_HIDDEN, $owner, $fields);
    }

    public function getHeaderFields($owner = null, $fields = null)
    {
        return $this->getFieldLocation(BaseField::LOCATION_HEADER, $owner, $fields);
    }

    public function getSubheaderFields($owner = null, $fields = null)
    {
        return $this->getFieldLocation(BaseField::LOCATION_SUBHEADER, $owner, $fields);
    }

    public function createColumnSchema($name, $settings = [])
    {
        $settings = array_merge($this->defaultCustomColumnSchema, $settings);
        $settings['name'] = $name;
        $settings['class'] = $this->columnSchemaClass;

        return Yii::createObject($settings);
    }

    public function getValidators()
    {
        $validators = parent::getValidators();

        if (is_object($this->objectType) && ($disabledFields = $this->objectType->disabledFields) && !empty($disabledFields)) {
            foreach ($validators as $validator) {
                $validator->attributes = array_diff($validator->attributes, $disabledFields);
            }
        }

        return $validators;
    }

    public function getField($field, $owner = null)
    {
        $fields = $this->fields;
        if (isset($fields[$field])) {
            return $fields[$field];
        } else {
            $functionName = 'get' . Inflector::camelize($field) . 'Field';
            if (method_exists($this, $functionName)) {
                return $this->$functionName();
            } elseif (isset($this->{$field})) {
                // create artificial field
                return $this->createArtificialField($field, $this->{$field}, $owner);
            }
        }

        return;
    }

    public function getRelationTaxonomies()
    {
        if (isset($this->relationModel) && isset($this->relationModel['id'])) {
            $parts = [];
            $relationClass = Yii::$app->classes['Relation'];
            $taxonomyClass = Yii::$app->classes['Taxonomy'];
            $relationModel = $relationClass::getRegisterModel($this->relationModel);
            //\d($relationModel->taxonomy_id);
            foreach ($relationModel->taxonomy_id as $taxonomy) {
                $taxonomyModel = $taxonomyClass::get($taxonomy, false);
                if ($taxonomyModel) {
                    $parts[] = $taxonomyModel->descriptor;
                }
            }
            if (empty($parts)) {
                return;
            }

            return implode(', ', $parts);
        }

        return;
    }

    public function getFilterFields()
    {
        $filters = [];
        foreach ($this->getFields() as $id => $field) {
            $filter = $field->filterSettings;
            if (!$filter) {
                continue;
            }
            $filter['id'] = $id;
            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * @param unknown $model                 (optional)
     * @param unknown $univeralFieldSettings (optional)
     *
     * @return unknown
     */
    public function getFields($owner = null)
    {
        if (!isset($this->_fields)) {
            $disabledFields = $this->objectType->disabledFields;
            $modelName = self::className();
            $this->_fields = [];
            $fieldSettings = $this->fieldSettings();
            $aliases = [];
            foreach (self::getTableSchema()->columns as  $name => $column) {
                if (in_array($name, $disabledFields)) {
                    continue;
                }
                $settings = [];
                if (isset($fieldSettings[$name])) {
                    $settings = array_merge_recursive($settings, $fieldSettings[$name]);
                }
                if (isset($settings['alias'])) {
                    $aliases[$name] = $settings['alias'];
                    unset($settings['alias']);
                }
                if (is_array($column)) {
                    $column = $this->createColumnSchema($name, $column);
                }
                $this->_fields[$name] = $this->createField($column, $owner, $settings);
            }

            $objectTypeItem = $this->objectTypeItem;
            if ($objectTypeItem) {
                $relationRelationship = null;
                $relationClass = Yii::$app->classes['Relation'];
                $taxonomies = $objectTypeItem->taxonomies;
                foreach ($objectTypeItem->parents as $relationship) {
                    $fieldName = 'parent:' . $relationship->parent->systemId;
                    if (in_array($fieldName, $disabledFields)) {
                        continue;
                    }
                    $fieldSchema = $this->createColumnSchema($fieldName, ['type' => 'relation', 'phpType' => 'object', 'dbType' => 'relation', 'allowNull' => true]);
                    $settings = [];
                    if (isset($fieldSettings[$fieldName])) {
                        $settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
                    }
                    if (isset($settings['alias'])) {
                        $aliases[$fieldName] = $settings['alias'];
                        unset($settings['alias']);
                    }
                    $settings['modelRole'] = 'child';
                    $settings['relationship'] = $relationship;
                    $this->_fields[$fieldName] = $this->createRelationField($fieldSchema, $owner, $settings);
                }

                foreach ($objectTypeItem->children as $relationship) {
                    $fieldName = 'child:' . $relationship->child->systemId;
                    if (in_array($fieldName, $disabledFields)) {
                        continue;
                    }
                    $fieldSchema = $this->createColumnSchema($fieldName, ['type' => 'relation', 'phpType' => 'object', 'dbType' => 'relation', 'allowNull' => true]);
                    $settings = [];
                    if (isset($fieldSettings[$fieldName])) {
                        $settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
                    }
                    if (isset($settings['alias'])) {
                        $aliases[$fieldName] = $settings['alias'];
                        unset($settings['alias']);
                    }
                    $settings['modelRole'] = 'parent';
                    $settings['relationship'] = $relationship;
                    $this->_fields[$fieldName] = $this->createRelationField($fieldSchema, $owner, $settings);
                }

                foreach ($taxonomies as $taxonomy) {
                    if (!in_array(self::className(), $taxonomy->models)) {
                        continue;
                    }

                    $fieldName = 'taxonomy:' . $taxonomy->systemId;
                    if (in_array($fieldName, $disabledFields)) {
                        continue;
                    }
                    $fieldSchema = $this->createColumnSchema($fieldName, ['type' => 'taxonomy', 'phpType' => 'object', 'dbType' => 'taxonomy', 'allowNull' => true]);

                    $settings = [];
                    if (isset($fieldSettings[$fieldName])) {
                        $settings = array_merge_recursive($settings, $fieldSettings[$fieldName]);
                    }
                    if (isset($settings['alias'])) {
                        $aliases[$fieldName] = $settings['alias'];
                        unset($settings['alias']);
                    }
                    $settings['model'] = $this;
                    $this->_fields[$fieldName] = $this->createTaxonomyField($fieldSchema, $taxonomy, $owner);
                }
            }

            foreach ($this->additionalFields() as $name => $column) {
                if (in_array($name, $disabledFields)) {
                    continue;
                }
                $settings = [];
                if (isset($fieldSettings[$name])) {
                    $settings = array_merge_recursive($settings, $fieldSettings[$name]);
                }
                if (isset($settings['alias'])) {
                    $aliases[$name] = $settings['alias'];
                    unset($settings['alias']);
                }
                if (is_string($column) && isset($this->_fields[$column])) {
                    $this->_fields[$name] = $this->duplicateField($name, $this->_fields[$column], $owner, $settings);
                } elseif (is_array($column)) {
                    $column = $this->createColumnSchema($name, $column);
                    $this->_fields[$name] = $this->createField($column, $owner, $settings);
                } else {
                    $this->_fields[$name] = $this->createField($column, $owner, $settings);
                }
            }
            foreach ($aliases as $from => $to) {
                if (isset($this->_fields[$to])) {
                    $this->_fields[$from] = $this->_fields[$to];
                }
            }
            $currentKeys = array_keys($this->_fields);
            foreach ($this->_fields as $name => $field) {
                if (!isset($field->priority)) {
                    $field->priority = (int) array_search($name, $currentKeys);
                    $field->priority = ($field->priority * 100);
                }
            }
            $this->_fields['relationTaxonomies'] = $this->createRelationTaxonomyField($owner, []);
            ArrayHelper::multisort($this->_fields, 'priority', SORT_ASC);
        }
        if (!empty($owner)) {
            foreach ($this->_fields as $field) {
                if (!$field->formField) {
                    continue;
                }
                $field->formField->owner = $owner;
            }
        }

        return $this->_fields;
    }

    public function duplicateField($name, $originalField, $owner, $settings = [])
    {
        $newField = clone $originalField;
        $newField->fieldSchema->name = $name;
        $newField->formField->owner = $owner;
        if ($newField->hasModel()) {
            $newField->model->tabularId = $name;
        }
        $newField->label = $this->getAttributeLabel($name);
        Yii::configure($newField, $settings);

        return $newField;
    }

    public function createField($fieldSchema, $owner, $settings = [])
    {
        $settings['class'] = $this->modelFieldClass;
        if (!isset($settings['model'])) {
            $settings['model'] = $this;
        }
        if (!is_object($fieldSchema)) {
            $fieldSchema = $this->createColumnSchema($fieldSchema);
        }
        $settings['fieldSchema'] = $fieldSchema;
        $settings['required'] = $this->isAttributeRequired($fieldSchema->name);

        if (!isset($settings['formField'])) {
            $settings['formField'] = [];
        }
        $settings['formField']['owner'] = $owner;

        return Yii::createObject($settings);
    }

    public function createArtificialField($fieldName, $fieldValue, $owner, $settings = [])
    {
        $settings['class'] = $this->artificialFieldClass;
        if (!isset($settings['model'])) {
            $settings['model'] = $this;
        }
        $settings['fieldName'] = $fieldName;
        $settings['fieldValue'] = $fieldValue;
        $settings['required'] = false;
        $settings['formField'] = false;

        return Yii::createObject($settings);
    }

    public function createRelationTaxonomyField($owner, $settings = [])
    {
        $settings['class'] = $this->relationTaxonomyFieldClass;
        if (!isset($settings['model'])) {
            $settings['model'] = $this;
        }
        $settings['fieldName'] = 'relationTaxonomies';
        $settings['required'] = false;
        $settings['formField'] = false;

        return Yii::createObject($settings);
    }

    public function createRelationField($fieldSchema, $owner, $settings = [])
    {
        $settings['class'] = $this->relationFieldClass;
        if (!isset($settings['baseModel'])) {
            $settings['baseModel'] = $this;
        }
        $settings['fieldSchema'] = $fieldSchema;
        $settings['required'] = $this->isAttributeRequired($fieldSchema->name);
        //$settings['baseModel'] = $this;

        if (!isset($settings['formField'])) {
            $settings['formField'] = [];
        }
        $settings['formField']['owner'] = $owner;

        return Yii::createObject($settings);
    }

    public function createTaxonomyField($fieldSchema, $taxonomy, $owner, $settings = [])
    {
        $settings['class'] = $this->taxonomyFieldClass;
        $settings['fieldSchema'] = $fieldSchema;
        if (!isset($settings['formField'])) {
            $settings['formField'] = [];
        }
        $settings['formField']['owner'] = $owner;
        $settings['taxonomy'] = $taxonomy;
        $settings['required'] = $taxonomy->required;

        return Yii::createObject($settings);
    }

    public function getObjectType()
    {
        $objectTypeItem = $this->objectTypeItem;
        if ($objectTypeItem) {
            return $objectTypeItem->object;
        }

        return false;
    }

    public function getObjectTypeItem()
    {
        if (isset(Yii::$app->collectors['types']) && Yii::$app->collectors['types']->has(get_class($this), 'object.primaryModel')) {
            return Yii::$app->collectors['types']->getOne(get_class($this), 'object.primaryModel');
        }

        return false;
    }
    /**
     * @return unknown
     */
    public function fieldSettings()
    {
        return;
    }

    public function formSettings($name, $settings = [])
    {
        if (!is_array($settings)) {
            $settings = [];
        }

        return $settings;
    }

    /**
     * @param unknown $key (optional)
     *
     * @return unknown
     */
    public function setFormValues($key = null)
    {
        if (!isset($_POST[get_class($this)])) {
            return true;
        }
        $base = $_POST[get_class($this)];
        if (is_null($key) or $key === 'primary') {
            if (!empty($base)) {
                $this->attributes = $base;
            }
        } else {
            $key = md5($key);
            if (!empty($base[$key])) {
                $this->attributes = $base[$key];
            }
        }

        return $this->validate();
    }
}
