<?php
/**
 * ./app/config/environments/templates/development/modules.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


$parent = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);
return array_merge(include($parent), [
	'debug' => [
		'class' => 'yii\debug\Module',
		'allowedIPs' => ['*']
	],
	'gii' => [
		'class' => 'yii\gii\Module',
		'allowedIPs' => ['*'],
		'generators' => [
			// 'cascadeSection' => [
			// 	'class' => '\cascade\gii\cascadeSectionModule\Generator'
			// ],
			'cascadeType' => [
				'class' => '\cascade\gii\cascadeTypeModule\Generator'
			]
		]
	]
]);
?>