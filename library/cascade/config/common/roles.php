<?php
return [
	'class' => '\infinite\security\role\Collector',
	'initial' => [
		'owner' => [
			'name' => 'Owner',
			'system_id' => 'owner',
			'system_version' => 1,
			'unique' => true,
			'level' => 1000,
			'acas' => null 	// can do everything!
		],
		'manager' => [
			'name' => 'Manager',
			'system_id' => 'manager',
			'system_version' => 1,
			'level' => 900,
			'acas' => ['read', 'update', 'manage_permissions']
		],
		'editor' => [
			'name' => 'Editor',
			'system_id' => 'editor',
			'system_version' => 1,
			'level' => 800,
			'acas' => ['read', 'update']
		],
		'commenter' => [
			'name' => 'Commenter',
			'system_id' => 'commenter',
			'system_version' => 1,
			'level' => 700,
			'acas' => ['read', 'comment']
		],
		'viewer' => [
			'name' => 'Viewer',
			'system_id' => 'viewer',
			'system_version' => 1,
			'level' => 600,
			'acas' => ['read']
		]
	]
];
?>