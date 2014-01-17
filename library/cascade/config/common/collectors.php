<?php
return [
	'class' => '\infinite\base\collector\Component',
	'collectors' => [
		'roles' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'roles.php'),
		'types' => [
			'class' => '\cascade\components\types\Collector',
		],
		'taxonomies' => [
			'class' => '\cascade\components\taxonomy\Collector',
		],
		
		'widgets' => [
			'class' => '\cascade\components\web\widgets\Collector',
			'lazyLoad' => true
		],
		'sections' => [
			'class' => '\cascade\components\section\Collector',
			'lazyLoad' => true
		],
		'dataInterfaces' => [
			'class' => '\cascade\components\dataInterface\Collector',
			'lazyLoad' => true
		]
	]
];
?>