<?php
/**
 * library/db/ActiveRecord.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package infinite
 */


namespace cascade\components\types;

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
			]
		];
	}

	public function getUrl($action = 'view') {
		return ['object/view', 'id' => $this->primaryKey];
	}
}


?>
