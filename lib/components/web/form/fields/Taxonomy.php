<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\form\fields;

use teal\helpers\ArrayHelper;

/**
 * Taxonomy [[@doctodo class_description:cascade\components\web\form\fields\Taxonomy]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Taxonomy extends Model
{
    /**
     * @inheritdoc
     */
    public function getFieldConfig()
    {
        $fieldConfig = parent::getFieldConfig();
        $fieldConfig['labelOptions']['label'] = $this->modelField->taxonomy->name;

        return $fieldConfig;
    }

    /**
     * {@inheritdocs}.
     */
    public function generate()
    {
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
