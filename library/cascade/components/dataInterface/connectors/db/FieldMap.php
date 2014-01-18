<?php
namespace cascade\components\dataInterface\connectors\db;

use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\KeyTranslation;
use cascade\components\dataInterface\Action;

use infinite\helpers\ArrayHelper;

class FieldMap extends \infinite\base\Object {
	public $map;

	public $localField = false;
	public $foreignField = false;
	public $foreignModel = false;

}
?>