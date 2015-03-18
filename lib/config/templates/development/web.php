<?php
/**
 * ./app/config/environments/templates/development/main.php.
 *
 * @author Jacob Morrison <jacob@canis.io>
 */
$parent = CANIS_APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);
$config = include $parent;
$config = array_merge($config, [
        'id' => '%%_.application_id%%',
        'name' => '%%general.application_name%%',
]);
$config['components']['assetManager']['linkAssets'] = true;
$config['components']['request']['cookieValidationKey'] = '%%_.cookie_salt%%';
$config['bootstrap'][] = 'debug';

return $config;
