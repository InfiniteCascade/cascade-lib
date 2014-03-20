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
		'editor' => [
			'name' => 'Editor',
			'systemId' => 'editor',
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