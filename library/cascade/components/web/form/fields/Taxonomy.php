<?php
namespace cascade\components\web\form\fields;

use infinite\db\ActiveRecord;
use infinite\helpers\ArrayHelper;

class Taxonomy extends Model {
	public function getFieldConfig()
	{
		$fieldConfig = parent::getFieldConfig();
		$fieldConfig['labelOptions']['label'] = $this->modelField->taxonomy->name;
		return $fieldConfig;
	}

	/**
	 * {@inheritdocs}
	 */
	public function generate() {
		$this->type = 'dropDownList';
		$baseOptions = [];
		if (!$this->modelField->taxonomy->required) {
			$baseOptions[''] = '';
		}
		$this->options = array_merge($baseOptions, ArrayHelper::map($this->modelField->taxonomy->taxonomies, 'id', 'name'));

		if ($this->modelField->taxonomy->multiple) {
			$this->htmlOptions['multiple'] = true;
		}
		return parent::generate();
	}
}
?>