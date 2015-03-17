<?php
return [
    'class' => 'canis\security\role\Collector',
    'initialItems' => [
        'owner' => [
            'name' => 'Owner',
            'systemId' => 'owner',
            'exclusive' => true,
            'inheritedEditable' => false,
            'level' => TEAL_ROLE_LEVEL_OWNER,
        ],
        'manager' => [
            'name' => 'Manager',
            'systemId' => 'manager',
            'level' => TEAL_ROLE_LEVEL_MANAGER,
        ],
        'editor' => [
            'name' => 'Editor',
            'systemId' => 'editor',
            'level' => TEAL_ROLE_LEVEL_EDITOR,
        ],
        'viewer' => [
            'name' => 'Viewer',
            'systemId' => 'viewer',
            'level' => TEAL_ROLE_LEVEL_VIEWER,
        ],
        'browser' => [
            'name' => 'Browser',
            'systemId' => 'browser',
            'level' => TEAL_ROLE_LEVEL_BROWSER,
        ],
    ],
];
