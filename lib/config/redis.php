<?php
/**
 * ./app/config/environments/common/cache.php.
 *
 * @author Jacob Morrison <jacob@canis.io>
 */

return [
    'class' => 'yii\redis\Connection',
    'hostname' => CANIS_APP_REDIS_HOST,
    'port' => CANIS_APP_REDIS_PORT,
    'database' => CANIS_APP_REDIS_DATABASE
];
