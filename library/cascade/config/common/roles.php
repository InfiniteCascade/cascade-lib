<?php
return [
	'class' => 'infinite\\security\\role\\Collector',
	'initialItems' => [
		'owner' => [
			'name' => 'Owner',
			'systemId' => 'owner'
		],
		'contributor' => [
			'name' => 'Contributor',
			'systemId' => 'contributor'
		],
		'viewer' => [
			'name' => 'Viewer',
			'systemId' => 'viewer'
		]
	]
];
?>