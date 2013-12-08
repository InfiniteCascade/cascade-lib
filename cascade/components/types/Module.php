<?php
/**
 * ./app/components/objects/RObjectModule.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */
namespace cascade\components\types;

use Yii;

use cascade\models\Group;
use cascade\models\Relation;
use cascade\models\Registry;
use cascade\models\ObjectFamiliarity;

use infinite\base\exceptions\Exception;
use infinite\base\exceptions\HttpException;
use infinite\base\language\Noun;
use infinite\db\ActiveRecord;

use yii\base\Controller;

abstract class Module extends \cascade\components\base\CollectorModule {
	protected $_title;
	public $version = 1;

	public $objectSubInfo = [];
	public $icon = 'ic-icon-info';
	public $priority = 1000; //lower is better

	public $uniparental = false;
	public $hasDashboard = true;

	public $sectionName;

	public $widgetNamespace;
	public $modelNamespace;

	public $formGeneratorClass = 'cascade\components\web\form\Generator';
	public $sectionClass = 'cascade\components\section\Section';

	public function init() {
		if (isset($this->modelNamespace)) {
			Yii::$app->registerModelAlias(':'. $this->systemId, $this->modelNamespace);
		}
		parent::init();
	}

	public function getCollectorName() {
		return 'types';
	}

	/**
	 *
	 *
	 * @param unknown $controller
	 * @param unknown $action
	 * @return unknown
	 */
	public function onBeforeControllerAction($controller, $action) {
		if (!isset($_SERVER['PASS_THRU']) or $_SERVER['PASS_THRU'] != md5(Yii::$app->params['salt'] . 'PASS')) {
			throw new HttpException(400, 'Invalid request!');
		}
		return parent::onBeforeControllerAction($event);
	}

	public function onAfterInit($event) {
		if (!isset(Yii::$app->collectors['taxonomies']) || !Yii::$app->collectors['taxonomies']->registerMultiple($this, $this->taxonomies())) { throw new Exception('Could not register taxonmies for '. $this->systemId .'!'); }
		if (!isset(Yii::$app->collectors['widgets']) || !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) { throw new Exception('Could not register widgets for '. $this->systemId .'!'); }
		if (!isset(Yii::$app->collectors['roles']) || !Yii::$app->collectors['roles']->registerMultiple($this, $this->roles())) { throw new Exception('Could not register roles for '. $this->systemId .'!'); }	
		return parent::onAfterInit($event);
	}

	
	public function setup() {
		$results = array(true);
		if (!empty($this->primaryModel) AND !empty($this->collectorItem->parents)) {
			$groups = array('staff');
			foreach ($groups as $groupName) {
				$group = Group::getBySystemName($groupName, true);
				if (empty($group)) { continue; }
				if ($this->isChildless) {
					$results[] = Yii::$app->gk->inherit(array('read', 'delete', 'create', 'archive', 'update'), null, $group, $this->primaryModel);
				} else {
					$results[] = Yii::$app->gk->parentAccess(array('read', 'delete', 'create', 'archive', 'update'), null, $group, $this->primaryModel);
				}
			}
		}
		return min($results);
	}

	public function getPrimaryModel() {
		return $this->modelNamespace .'\\'. 'Object'.$this->systemId;
	}

	public function getModuleType() {
		return 'Type';
	}

	public function upgrade($from) {
		return true;
	}

	public function getPossibleRoles() {
		return Yii::$app->collectors['roles']->getRoles($this);
	}

	public function getPossibleRoleList() {
		return Yii::$app->collectors['roles']->getRoleList($this);
	}

	public function getCreatorRole() {
		return [];
	}

	public function getIsOwnable() {
		return false;
	}

	public function getOwnerObject() {
		return null;
	}

	public function getOwner() {
		if (!$this->isOwnable) {
			return null;
		}
		$ownerObject = $this->getOwnerObject();
		if (is_object($ownerObject)) {
			return $ownerObject->primaryKey;
		}
		return $ownerObject;
	}

	/**
	 *
	 *
	 * @param unknown $term
	 * @param unknown $limit (optional)
	 * @return unknown
	 */
	public function search($term, $params = []) {
		throw new Exception("Who is calling this?");
		if (!$this->primaryModel) { return false; }
		$results = [];
		$model = $this->primaryModel;
		$model = new $model('search');
		$raw = $model->searchTerm($term, $params);
		return $raw;
	}

	public function getObjectLevel() {
		if ($this->isPrimaryType) {
			return 1;
		}
		$parents = $this->collectorItem->parents;
		if (!empty($parents)) {
			$maxLevel = 1;
			foreach ($parents as $rel) {
				if (get_class($rel->parent) === get_class($this)) { continue; }
				$newLevel = $rel->parent->objectLevel + 1;
				if ($newLevel > $maxLevel) {
					$maxLevel = $newLevel;
				}
			}
			return $maxLevel;
		}
		return 1;
	}
	/**
	 *
	 *
	 * @param unknown $parent   (optional)
	 * @param unknown $settings (optional)
	 * @return unknown
	 */
	public function getSection($parent = null, $settings = []) {
		$name = $this->systemId;
		if (!empty($parent) and $parent->systemId === $this->systemId) {
			$sectionId = $settings['whoAmI'].'-'.$this->systemId;
			$section = Yii::$app->collectors['sections']->getOne($sectionId);
			if (empty($section->object)) {
				$sectionConfig = ['class' => $this->sectionClass, 'title' => 'Related %%type.'. $this->systemId .'.title.upperPlural%%', 'icon' => $this->icon, 'systemId' => $sectionId];
				$section->displayPriority = -999;
				$section->object = Yii::createObject($sectionConfig);
			}
			return $section;
		}
		$newSectionTitle = '%%type.'. $this->systemId .'.title.upperPlural%%';
		if (!is_null($this->sectionName)) {
			$sectionClass = $this->sectionClass;
			$sectionId = $sectionClass::generateSectionId($this->sectionName);
			if (Yii::$app->collectors['sections']->has($sectionId)) {
				$section = Yii::$app->collectors['sections']->getOne($sectionId);
				return $section;
			}
			$newSectionTitle = $this->sectionName;
		}
		$section = Yii::$app->collectors['sections']->getOne($this->systemId);
		if (empty($section->object)) {
			$sectionConfig = ['class' => $this->sectionClass, 'title' => $newSectionTitle, 'icon' => $this->icon, 'systemId' => $this->systemId];
			$section->object = Yii::createObject($sectionConfig);
		}
		return $section;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTitle() {
		if (!is_object($this->_title)) {
			$this->_title = new Noun($this->_title);
		}
		return $this->_title;
	}

	public function setTitle($title) {
		$this->_title = $title;
	}


	public function widgets() {
		$widgets = [];
		$detailListClassName = self::classNamespace() .'\widgets\\'. 'DetailList';
		$simpleListClassName = self::classNamespace() .'\widgets\\'. 'SimpleLinkList';
		$embeddedListClassName = self::classNamespace() .'\widgets\\'. 'EmbeddedList';
		@class_exists($detailListClassName);
		@class_exists($simpleListClassName);
		@class_exists($embeddedListClassName);

		$baseWidget = [];
		if (!($this->module instanceof \infinite\base\ApplicationInterface)) {
			$baseWidget['section'] = $this->module->collectorItem;
		}
		
		if (!$this->isChildless) {
			if (!class_exists($detailListClassName, false)) { $detailListClassName = false; }
			if (!class_exists($simpleListClassName, false)) { $simpleListClassName = false; }
			if (!class_exists($embeddedListClassName, false)) { $embeddedListClassName = false; }
			// needs widget for children and summary page
			if ($detailListClassName) {
				$childrenWidget = $baseWidget;
				$id = 'Parent'. $this->systemId .'Browse';
				$childrenWidget['widget'] = [
					'class' => $detailListClassName,
					'icon' => $this->icon, 
					'title' => '%%relationship%% %%type.'. $this->systemId .'.title.upperPlural%%'
				];
				$childrenWidget['locations'] = array('child_objects');
				$childrenWidget['displayPriority'] = $this->priority;
				$widgets[$id] = $childrenWidget;
			} else {
				Yii::trace("Warning: There is no browse class for the child objects of {$this->systemId}");
			}
			if ($this->hasDashboard && $simpleListClassName) {
				$summaryWidget = $baseWidget;
				$id = $this->systemId .'Summary';
				$summaryWidget['widget'] = [
					'class' => $simpleListClassName,
					'icon' => $this->icon, 
					'title' => '%%type.'. $this->systemId .'.title.upperPlural%%'
				];
				$summaryWidget['locations'] = array('front');
				$summaryWidget['displayPriority'] = $this->priority;
				$widgets[$id] = $summaryWidget;
			} else {
				Yii::trace("Warning: There is no summary class for {$this->systemId}");
			}
		} else {
			if (!class_exists($detailListClassName, false)) { $detailListClassName = false; }
			if (!class_exists($embeddedListClassName, false)) { $embeddedListClassName = false; }
			// needs widget for parents
		}
		if ($embeddedListClassName) {
			$childrenWidget = $baseWidget;
			$id = 'Embedded'. $this->systemId .'Browse';
			$childrenWidget['widget'] = [
				'class' => $embeddedListClassName,
				'icon' => $this->icon, 
				'title' => '%%type.'. $this->systemId .'.title.upperPlural%%'
			];
			$childrenWidget['locations'] = array('parent_objects', 'child_objects');
			$childrenWidget['displayPriority'] = $this->priority;
			$widgets[$id] = $childrenWidget;
		} else {
			Yii::trace("Warning: There is no browse class for the child objects of {$this->systemId}");
		}
		if ($detailListClassName) {
			$parentsWidget = $baseWidget;
			$id = 'Children'. $this->systemId .'Browse';
			$parentsWidget['widget'] = [
					'class' => $detailListClassName,
					'icon' => $this->icon, 
					'title' => '%%relationship%% %%type.'. $this->systemId .'.title.upperPlural%%'
				];
			$parentsWidget['locations'] = array('parent_objects');
			$parentsWidget['displayPriority'] = $this->priority + 1;
			$widgets[$id] = $parentsWidget;
		} else {
			Yii::trace("Warning: There is no browse class for the parent objects of {$this->systemId}");
		}
		return $widgets;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function taxonomies() {
		return [];
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function roles() {
		return [];
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function dependencies() {
		return [];
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function parents() {
		return [];
	}


	/**
	 * Settings for 
	 *
	 * @return unknown
	 */
	public function parentSettings() {
		return array(
			'title' => false,
			'allow' => 1, // 0/false = no; 1 = only 1; 2 = 1 or more
			'showDescriptor' => false
		);
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function childrenSettings() {
		return array(
			'allow' => 2,  // 0/false = no; 1 = only 1; 2 = 1 or more
		);
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function children() {
		return [];
	}



	public function getDummyModel() {
		if (!$this->primaryModel) { return false; }
		$model = $this->primaryModel;
		return new $model;
	}

	public function getIsChildless() {
		if (empty($this->collectorItem) OR empty($this->collectorItem->children)) {
			return true;
		}
		return false;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getModel($primaryModel = null, $input = []) {
		if (is_null($primaryModel)) {
			$primaryModel = new $this->primaryModel;
		}
		
		$formName = $primaryModel->formName();
		if (!empty($input) && isset($input[$formName]['_moduleHandler'])) {
			$moduleHandler = $input[$formName]['_moduleHandler'];
			$primaryModel->_moduleHandler = $moduleHandler;
			unset($input[$formName]['_moduleHandler']);
			$primaryModel->load($input);
		}
		return $primaryModel;
	}

	public function getModels($primaryModel = null) {
		$model = $this->getModel($primaryModel);
		return [$model->tabularId => $model];
	}


	/**
	 *
	 *
	 * @param unknown $models (optional)
	 * @return unknown
	 */
	public function handleSave($model) {
		if ($this->internalSave($model)) {
			ObjectFamiliarity::created($model);
			return true;
		}
		return false;
	}

	protected function internalSave($model) {
		return $model->save();
	}

	public function handleSaveAll($input = null, $settings = []) {
		if (is_null($input)) {
			$input = $this->_handlePost($settings);
		}
		$error = false;
		$notice = [];
		$models = false;
		if ($input) {
			$models = $this->_extractModels($input);
			$isValid = true;
			foreach ($models as $model) {
				if (!$model->validate()) {
					$isValid = false;
				}
			}
			if ($isValid) {
				// save primary
				$primary = $input['primary'];
				if (isset($primary['handler'])) {
					$result = $primary['handler']->handleSave($primary['model']);
				} else {
					$result = $this->internalSave($primary['model']);
				}
				if (!$result || empty($primary['model']->primaryKey)) {
					$error = 'An error occurred while saving.';
				} else {
					// loop through parents
					foreach ($input['parents'] as $parentKey => $parent) {
						$relation = $parent['model']->getRelationModel($parentKey);
						$relation->child_object_id = $parent['model']->primaryKey;
						if (isset($parent['handler'])) {
							$descriptor = $parent['handler']->title->singular;
							$result = $parent['handler']->handleSave($parent['model']);
						} else {
							$descriptor = 'part of the record';
							$result = $this->internalSave($parent['model']);
						}
						if (!$result) {
							$noticeMessage = 'Unable to save '. $descriptor;
							if (!in_array($noticeMessage, $notice)) {
								$notice[] = $noticeMessage;
							}
						}
					}

					// loop through children
					foreach ($input['children'] as $childKey => $child) {
						$relation = $child['model']->getRelationModel($childKey);
						$relation->parent_object_id = $primary['model']->primaryKey;

						if (isset($child['handler'])) {
							$descriptor = $child['handler']->title->singular;
							$result = $child['handler']->handleSave($child['model']);
						} else {
							$descriptor = 'part of the record';
							$result = $this->internalSave($child['model']);
						}

						if (!$result) {
							$noticeMessage = 'Unable to save '. $descriptor;
							if (!in_array($noticeMessage, $notice)) {
								$notice[] = $noticeMessage;
							}
						}
					}
				}
			} else {
				$error = 'Please fix the entry errors.';
			}
		} else {
			$error = 'Invalid input!';
		}
		if (empty($notice)) { 
			$notice = false;
		} else {
			$notice = implode('; ', $notice);
		}
		return [$error, $notice, $models, $input];
	}

	protected function _extractModels($input) {
		if ($input === false) { return false; }
		$models = [];
		if (isset($input['primary'])) {
			$models[$input['primary']['model']->tabularId] = $input['primary']['model'];
		}
		if (!empty($input['children'])) {
			foreach ($input['children'] as $child) {
				$models[$child['model']->tabularId] = $child['model'];
			}
		}
		if (!empty($input['parents'])) {
			foreach ($input['parents'] as $parent) {
				$models[$parent['model']->tabularId] = $parent['model'];
			}
		}
		return $models;
	}

	protected function _handlePost($settings = []) {
		var_dump('post');
		$results = ['primary' => null, 'children' => [], 'parents' => []];
		if (empty($_POST)) { return false; }

		foreach ($_POST as $modelTop => $tabs) {
			if (!is_array($tabs)) { continue; }
			foreach ($tabs as $tabId => $tab) {
				if (!isset($tab['_moduleHandler'])) { continue; }
				$m = [$modelTop => $tab];
				$object = null;
				if (isset($tab['id'])) {
					$object = $this->params['object'] = Registry::getObject($tab['id']);
					if (!$object) {
						throw new HttpException(404, "Unknown object.");
					}
					if (!$object->can('update')) {
						throw new HttpException(403, "Unable to update object.");
					}
				}
				if ($tab['_moduleHandler'] === ActiveRecord::FORM_PRIMARY_MODEL) {
					if (isset($results['primary'])) {
						return false;
					}
					$results['primary'] = ['handler' => $this, 'model' => $this->getModel($object, $m)];
					continue;
				}
				$handlerParts = explode(':', $tab['_moduleHandler']);
				if (count($handlerParts) >= 3) {
					$resultsKey = null;
					if ($handlerParts[0] === 'child') {
						$rel = $this->collectorItem->getChild($handlerParts[1]);
						if (!$rel || !($handler = $rel->child)) { continue; }
						$resultsKey = 'children';
					} elseif ($handlerParts[0] === 'parent') {
						$handler = $this->collectorItem->getParent($handlerParts[1]);
						$rel = $this->collectorItem->getParent($handlerParts[1]);
						if (!$rel || !($handler = $rel->parent)) { continue; }
						$resultsKey = 'parents';
					}
					if (!empty($resultsKey)) {
						$model = $handler->getModel($object, $m);
						$dirty = $model->getDirtyAttributes();
						if ($model->isNewRecord) {
							$formName = $model->formName();

							foreach ($m[$formName] as $k => $v) {
								if (empty($v)) {
									unset($dirty[$k]);
								}
							}
						}
						if (!empty($settings['allowEmpty']) || count($dirty) > 0) {
							$relation = $model->getRelationModel(implode(':', array_slice($handlerParts, 0, 2)));
							$relationFormClass = $relation->formName();
							$relationTabularId = $relation->tabularId;
							if (isset($_POST[$relationFormClass][$relationTabularId])) {
								$relation->attributes = $_POST[$relationFormClass][$relationTabularId];
							}
							$results[$resultsKey][$tabId] = ['handler' => $handler, 'model' => $model];
						}
					}
				}
			}
		}
		var_dump("nopost");
		if (is_null($results['primary'])) { return false; }
		return $results; 
	}


	/**
	 *
	 *
	 * @param unknown $primaryModel (optional)
	 * @return unknown
	 */
	public function getForm($models = null, $settings = []) {
		$primaryModel = ActiveRecord::getPrimaryModel($models);
		if (!$primaryModel) { return false; }
		$formSegments = [$this->getFormSegment($primaryModel, $settings)];
		$config = ['class' => $this->formGeneratorClass, 'items' => $formSegments, 'models' => $models];
		return Yii::createObject($config);
	}

	public function getFormSegment($primaryModel = null, $settings = [])
	{
		if (empty($primaryModel)) {
			return false;
		}
		return $primaryModel->form($settings);
	}
}


?>
