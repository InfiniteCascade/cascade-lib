<?php
return [
    'class' => 'infinite\security\role\Collector',
    'initialItems' => [
        'owner' => [
            'name' => 'Owner',
            'systemId' => 'owner',
            'exclusive' => true,
            'inheritedEditable' => false,
            'level' => INFINITE_ROLE_LEVEL_OWNER,
        ],
        'manager' => [
            'name' => 'Manager',
            'systemId' => 'manager',
            'level' => INFINITE_ROLE_LEVEL_MANAGER,
        ],
        'editor' => [
            'name' => 'Editor',
            'systemId' => 'editor',
            'level' => INFINITE_ROLE_LEVEL_EDITOR,
        ],
        'viewer' => [
            'name' => 'Viewer',
            'systemId' => 'viewer',
            'level' => INFINITE_ROLE_LEVEL_VIEWER,
        ],
        'browser' => [
            'name' => 'Browser',
            'systemId' => 'browser',
            'level' => INFINITE_ROLE_LEVEL_BROWSER,
        ],
    ],
];
