<?php
/**
 * ./app/config/environments/templates/development/cache.php.
 *
 * @author Jacob Morrison <jacob@canis.io>
 */
$parent = TEAL_APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);

return array_merge(include($parent), []);
