<?php
return [
	'class' => 'infinite\\security\\role\\Collector',
	'initialItems' => [
		'owner' => [
			'name' => 'Owner',
			'systemId' => 'owner',
			'exclusive' => true,
			'level' => 400
		],
		'manager' => [
			'name' => 'Manager',
			'systemId' => 'manager',
			'level' => 300
		],
		'contributor' => [
			'name' => 'Contributor',
			'systemId' => 'contributor',
			'level' => 200
		],
		'viewer' => [
			'name' => 'Viewer',
			'systemId' => 'viewer',
			'level' => 100
		]
	]
];
?>