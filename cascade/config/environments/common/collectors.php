<?php
return [
	'class' => '\infinite\base\collector\Component',
	'collectors' => [
		'roles' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'roles.php'),
		'widgets' => [
			'class' => '\cascade\components\web\widgets\Collector',
		],
		'types' => [
			'class' => '\cascade\components\types\Collector',
		],
		'sections' => [
			'class' => '\cascade\components\section\Collector',
		],
		'taxonomies' => [
			'class' => '\cascade\components\taxonomy\Collector',
		],
		'dataInterfaces' => [
			'class' => '\cascade\components\dataInterface\Collector',
		]
	]
];
?>