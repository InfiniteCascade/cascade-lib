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
 * RelationTaxonomy [[@doctodo class_description:cascade\components\db\fields\RelationTaxonomy]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationTaxonomy extends Base
{
    /**
     * @var [[@doctodo var_type:fieldName]] [[@doctodo var_description:fieldName]]
     */
    public $fieldName;

    /**
     * @var [[@doctodo var_type:human]] [[@doctodo var_description:human]]
     */
    public $human = false;

    /**
     * @var [[@doctodo var_type:multiline]] [[@doctodo var_description:multiline]]
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

    /**
     * @inheritdoc
     */
    public function determineFormatClass()
    {
        return RawText::className();
    }

    /**
     * @inheritdoc
     */
    public function getFilterSettings()
    {
        return false;
    }
}
