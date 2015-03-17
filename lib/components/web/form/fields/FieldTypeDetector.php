<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\form\fields;

use cascade\components\db\fields\Base as DbBaseField;

/**
 * FieldTypeDetector [[@doctodo class_description:cascade\components\web\form\fields\FieldTypeDetector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class FieldTypeDetector extends \canis\base\object
{
    /**
     * [[@doctodo method_description:detect]].
     *
     * @param cascade\components\db\fields\Base $field [[@doctodo param_description:field]]
     *
     * @return [[@doctodo return_type:detect]] [[@doctodo return_description:detect]]
     */
    public static function detect(DbBaseField $field)
    {
        if (!$field->human) {
            return 'hidden';
        } else {
            $fieldType = $type = 'text';
            $dbMap = ['date' => 'date'];

            $fieldSchema = $field->fieldSchema;
            if ($fieldSchema->dbType === 'tinyint(1)') {
                return 'boolean';
            }
            if ($field->multiline) {
                return 'textarea';
            } elseif ($fieldSchema->type === 'boolean') {
                return 'boolean';
            } elseif (isset($dbMap[$fieldSchema->dbType])) {
                return $dbMap[$fieldSchema->dbType];
            }

            return 'text';
        }
    }
}
