<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

use cascade\components\db\fields\formats\RawText;

/**
 * Taxonomy [@doctodo write class description for Taxonomy].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationTaxonomy extends Base
{
    public $fieldName;

    /**
     */
    public $human = false;

    /**
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
