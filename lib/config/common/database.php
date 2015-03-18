<?php
/**
 * ./app/config/environments/common/database.php.
 *
 * @author Jacob Morrison <jacob@canis.io>
 */

return [
    'class' => 'canis\db\Connection',
    'dsn' => 'mysql:host=' . CANIS_APP_DATABASE_HOST . ';port=' . CANIS_APP_DATABASE_PORT . ';dbname=' . CANIS_APP_DATABASE_DBNAME . '',
    'emulatePrepare' => true,
    'username' => CANIS_APP_DATABASE_USERNAME,
    'password' => CANIS_APP_DATABASE_PASSWORD,
    'charset' => 'utf8',
    'enableSchemaCache' => true,

];
