<?php
/**
 * ./app/config/environments/common/params.php.
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 */

return [
    'siteName' => 'Cascade',
    'sessionExpiration' => 3600,
    'defaultCountry' => 'US',
    'currency' => 'usd',
    'defaultSubnationalDivision' => null,
    'migrationAliases' => ['@cascade/migrations'],
    // site look
    'logoLogin' => "/themes/ic/img/cascade-logo-450.png",
    'logoSmall' => "/themes/ic/img/cascade-logo-75.png",
    'helperUrls' => [
        'map' => 'http://maps.google.com/?q=%%object.flatAddressUrl%%',
    ],
    'moduleConfig' => [
    ],
    'defaultStorageEngine' => 'local',
    'maxRoleLevels' => [
        'public' => INFINITE_ROLE_LEVEL_VIEWER, // viewer
        'primaryAccount' => INFINITE_ROLE_LEVEL_MANAGER, // manager
    ],
];
