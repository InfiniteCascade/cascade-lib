<?php
namespace cascade\components\types;

interface ActiveRecordInterface {
	public function getUrl($action = 'view');
	public function getDefaultValues();
	public function loadDefaultValues();
}


?>
