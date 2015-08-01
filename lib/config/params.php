<?php
/**
 * ./app/config/environments/common/params.php.
 *
 * @author Jacob Morrison <jmorrison@psesd.org>
 */

return [
    'siteName' => 'Cascade',
    'sessionExpiration' => 3600,
    'defaultCountry' => 'US',
    'currency' => 'usd',
    'defaultSubnationalDivision' => null,
    'migrationAliases' => ['@canis/db/migrations', '@cascade/migrations'],
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
        'public' => CANIS_ROLE_LEVEL_VIEWER, // viewer
        'primaryAccount' => CANIS_ROLE_LEVEL_MANAGER, // manager
    ],
];
