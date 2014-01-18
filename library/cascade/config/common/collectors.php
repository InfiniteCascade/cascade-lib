<?php
$lazyLoad = !(defined('IS_CONSOLE') && IS_CONSOLE);

return [
	'class' => 'infinite\\base\\collector\\Component',
	'collectors' => [
		'roles' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'roles.php'),
		'types' => [
			'class' => 'cascade\\components\\types\Collector',
		],
		'taxonomies' => [
			'class' => 'cascade\\components\\taxonomy\\Collector',
		],
		
		'widgets' => [
			'class' => 'cascade\\components\\web\\widgets\\Collector',
			'lazyLoad' => $lazyLoad
		],
		'sections' => [
			'class' => 'cascade\\components\\section\\Collector',
			'lazyLoad' => $lazyLoad
		],
		'dataInterfaces' => [
			'class' => 'cascade\\components\\dataInterface\\Collector',
			'lazyLoad' => $lazyLoad
		]
	]
];
?>