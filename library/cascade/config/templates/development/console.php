<?php
/**
 * ./app/config/environments/templates/development/console.php.
 *
 * @author Jacob Morrison <jacob@tealcascade.com>
 */
$parent = TEAL_APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);
$config = include $parent;
$config = array_merge($config, [
        'id' => '%%_.application_id%%',
        'name' => '%%general.application_name%%',
    ]);

return $config;
