<?php
return [
    'class' => 'canis\security\role\Collector',
    'initialItems' => [
        'owner' => [
            'name' => 'Owner',
            'systemId' => 'owner',
            'exclusive' => true,
            'inheritedEditable' => false,
            'level' => CANIS_ROLE_LEVEL_OWNER,
        ],
        'manager' => [
            'name' => 'Manager',
            'systemId' => 'manager',
            'level' => CANIS_ROLE_LEVEL_MANAGER,
        ],
        'editor' => [
            'name' => 'Editor',
            'systemId' => 'editor',
            'level' => CANIS_ROLE_LEVEL_EDITOR,
        ],
        'viewer' => [
            'name' => 'Viewer',
            'systemId' => 'viewer',
            'level' => CANIS_ROLE_LEVEL_VIEWER,
        ],
        'browser' => [
            'name' => 'Browser',
            'systemId' => 'browser',
            'level' => CANIS_ROLE_LEVEL_BROWSER,
        ],
    ],
];
