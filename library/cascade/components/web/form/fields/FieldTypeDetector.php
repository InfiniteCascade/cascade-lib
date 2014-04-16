<?php
namespace cascade\components\web\form\fields;

use cascade\components\db\fields\Base as DbBaseField;

class FieldTypeDetector extends \infinite\base\object
{
    static public function detect(DbBaseField $field)
    {
        if (!$field->human) {
            return 'hidden';
        } else {
            $fieldType = $type = 'text';
            $dbMap = ['date' => 'date'];

            $fieldSchema = $field->fieldSchema;
            if ($field->multiline) {
                $type = 'textarea';
            } elseif ($fieldSchema->type === 'boolean') {
                $type = 'boolean';
            } elseif (isset($dbMap[$fieldSchema->dbType])) {
                $type = $dbMap[$fieldSchema->dbType];
            }

            return $type;
        }
    }
}
