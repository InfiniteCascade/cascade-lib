<?php
/**
 * ./app/config/environments/templates/development/roles.php.
 *
 * @author Jacob Morrison <jacob@tealcascade.com>
 */
$parent = TEAL_APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);
$idp = include $parent;
if (!isset($idp['initialItems'])) {
    $idp['initialItems'] = [];
}

return $idp;
