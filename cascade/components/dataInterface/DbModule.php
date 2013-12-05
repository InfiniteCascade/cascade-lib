<?php
namespace cascade\components\dataInterface;

abstract class DbModule extends Module {
	abstract public function getForeignSchema();
}
?>