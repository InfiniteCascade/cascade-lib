<?php
namespace cascade\components\taxonomy;

use Yii;

use infinite\base\exceptions\Exception;

class Collector extends \infinite\base\collector\Module
{
	const EVENT_AFTER_TAXONOMY_REGISTRY = 'afterTaxonomyRegistry';

	public $taxonomyClass = 'cascade\models\Taxonomy';
	public $taxonomyTypeClass = 'cascade\models\TaxonomyType';

	public function getCollectorItemClass() {
		return 'cascade\components\taxonomy\Item';
	}

	public function getModulePrefix() {
		return 'TaxonomyType';
	}

	public function register($owner, $itemComponent, $systemId = null) {
		if ($itemComponent instanceof Module) {
			return parent::register($owner, $itemComponent->settings, $systemId);
		}
		return parent::register($owner, $itemComponent, $systemId);
	}

	public function mergeExistingItems($originalItem, $newItem) {
		$originalItem = parent::mergeExistingItems($originalItem, $newItem);
		$originalItem->models = array_unique(array_merge($originalItem->models, $newItem->models));
		$originalItem->modules = array_unique(array_merge($originalItem->modules, $newItem->modules));
		if ($newItem->systemVersion > $originalItem->systemVersion) {
			if ($this->initializeTaxonomies($originalItem->object, $newItem->initialTaxonomies)) {
				$originalItem->object->systemVersion = $newItem->systemVersion;
				$originalItem->object->save(true, ['systemVersion']);
			}

		}
		return $originalItem;
	}

	public function prepareComponent($component) {
		if (!Yii::$app->isDbAvailable) { return $component; }
		
		$taxonomyTypeClass = $this->taxonomyTypeClass;
		$component['object'] = $taxonomyTypeClass::findOne(['system_id' => $component['systemId']]);
		if (empty($component['object'])) {
			$component['object'] = new $taxonomyTypeClass;
			$component['object']->name = $component['name'];
			$component['object']->system_id = $component['systemId'];
			$component['object']->system_version = 0;
			if (!$component['object']->save()) {
				throw new Exception("Couldn't save new taxonomy type {$component['systemId']} ". print_r($component['object']->getFirstErrors(), true));
			}
			Yii::trace("Taxonomy type has been initialized {$component['name']} ({$component['systemId']})");
		}

		if (!isset($component['initialTaxonomies'])) { $component['initialTaxonomies'] = []; }
		
		if ($component['object']->system_version < $component['systemVersion']) {
			if ($this->initializeTaxonomies($component['object'], $component['initialTaxonomies'])) {
				$component['object']->system_version = $component['systemVersion'];
				if (!$component['object']->save()) {
					throw new Exception("Couldn't save new taxonomy type {$component['systemId']} with new version");
				}
				Yii::trace("Taxonomy type has been upgraded {$component['name']} ({$component['systemId']}) to version {$component['systemVersion']}");
			} else {
				throw new Exception("Couldn't upgrade taxonomy type {$component['systemId']} to version {$component['systemVersion']}");
			}
		}
		return $component;
	}

	public function initializeTaxonomies($model, $taxonomies) {
		$taxonomyClass = $this->taxonomyClass;
		foreach ($taxonomies as $systemId => $name) {
			$taxonomy = $taxonomyClass::findOne(['taxonomy_type_id' => $model->id, 'system_id' => $systemId]);
			if (empty($taxonomy)) {
				$taxonomy = new $taxonomyClass;
				$taxonomy->taxonomy_type_id = $model->id;
				$taxonomy->name = $name;
				$taxonomy->system_id = $systemId;
				if (!$taxonomy->save()) {
					return false;
				}
			}
		}
		return true;
	}
}
?>