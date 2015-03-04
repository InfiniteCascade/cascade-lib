<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use cascade\components\db\fields\formats\RawText;

/**
 * Taxonomy [@doctodo write class description for Taxonomy]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationTaxonomy extends Base
{
    public $fieldName;

    /**
     * @var __var_human_type__ __var_human_description__
     */
    public $human = false;

    /**
     * @var __var_multiline_type__ __var_multiline_description__
     */
    public $multiline = false;

    /**
    * @inheritdoc
     */
    public function getField()
    {
        return $this->fieldName;
    }

    /**
    * @inheritdoc
     */
    public function setFormField($value)
    {
        $this->_formField = false;
    }

    /**
    * @inheritdoc
     */
    public function getFormField()
    {
        return false;
    }

    public function determineFormatClass()
    {
        return RawText::className();
    }

    public function getFilterSettings()
    {
        return false;
    }
}
