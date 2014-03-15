<?php
return [
	'class' => 'infinite\\security\\role\\Collector',
	'initialItems' => [
		'owner' => [
			'name' => 'Owner',
			'systemId' => 'owner',
			'exclusive' => true
		],
		'manager' => [
			'name' => 'Manager',
			'systemId' => 'manager'
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