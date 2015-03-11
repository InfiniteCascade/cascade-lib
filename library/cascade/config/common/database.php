<?php
/**
 * ./app/config/environments/common/database.php.
 *
 * @author Jacob Morrison <jacob@tealcascade.com>
 */

return [
    'class' => 'teal\db\Connection',
    'dsn' => 'mysql:host=' . TEAL_APP_DATABASE_HOST . ';port=' . TEAL_APP_DATABASE_PORT . ';dbname=' . TEAL_APP_DATABASE_DBNAME . '',
    'emulatePrepare' => true,
    'username' => TEAL_APP_DATABASE_USERNAME,
    'password' => TEAL_APP_DATABASE_PASSWORD,
    'charset' => 'utf8',
    'enableSchemaCache' => true,

];
