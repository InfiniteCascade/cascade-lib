<?php
/**
 * library/db/ActiveRecord.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package infinite
 */


namespace cascade\components\types;

use infinite\helpers\Html;

trait ActiveRecordTrait {
	public function behaviors() {
		return [
			'Registry' => [
				'class' => 'infinite\\db\\behaviors\\Registry',
				'registryClass' => 'cascade\\models\\Registry',
			],
			'Relatable' => [
				'class' => 'infinite\\db\\behaviors\\Relatable',
				'relationClass' => 'cascade\\models\\Relation',
				'registryClass' => 'cascade\\models\\Registry',
			],
			'Taxonomy' => [
				'class' => 'cascade\\components\\db\\behaviors\\Taxonomy',
			]
		];
	}

	public function getUrl($action = 'view') {
		return ['object/'. $action, 'id' => $this->primaryKey];
	}

	public function getViewLink()
	{
		return Html::a($this->descriptor, $this->getUrl('view'));
	}
}


?>
