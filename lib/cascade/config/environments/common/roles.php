<?php
return array(
	'class' => '\infinite\security\role\Collector',
	'initial' => array(
		'owner' => array(
			'name' => 'Owner',
			'system_id' => 'owner',
			'system_version' => 1,
			'unique' => true,
			'level' => 1000,
			'acas' => null 	// can do everything!
		),
		'manager' => array(
			'name' => 'Manager',
			'system_id' => 'manager',
			'system_version' => 1,
			'level' => 900,
			'acas' => array('read', 'update', 'manage_permissions')
		),
		'editor' => array(
			'name' => 'Editor',
			'system_id' => 'editor',
			'system_version' => 1,
			'level' => 800,
			'acas' => array('read', 'update')
		),
		'commenter' => array(
			'name' => 'Commenter',
			'system_id' => 'commenter',
			'system_version' => 1,
			'level' => 700,
			'acas' => array('read', 'comment')
		),
		'viewer' => array(
			'name' => 'Viewer',
			'system_id' => 'viewer',
			'system_version' => 1,
			'level' => 600,
			'acas' => array('read')
		)
	)
);
?>