<?php
return [
    'class' => 'teal\security\identity\providers\Collector',
    'initialItems' => [],
    'handlers' => [
        'Ldap' => [
            'class' => 'teal\security\identity\providers\Ldap',
        ],
    ],
];
