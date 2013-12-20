<?php
namespace cascade\components\web\form\fields;

use infinite\helpers\Html;
use infinite\helpers\Match;
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
			} elseif (isset($dbMap[$fieldSchema->dbType])) {
				$type = $dbMap[$fieldSchema->dbType];
			}
			return $type;
		}
	}
}