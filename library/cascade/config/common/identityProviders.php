<?php
return [
    'class' => 'infinite\\security\\identity\\providers\\Collector',
    'initialItems' => [],
    'handlers' => [
        'Ldap' => [
            'class' => 'infinite\\security\\identity\\providers\\Ldap'
        ]
    ]
];
