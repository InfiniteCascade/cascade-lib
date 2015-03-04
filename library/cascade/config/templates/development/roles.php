<?php
/**
 * ./app/config/environments/templates/development/roles.php.
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 */
$parent = INFINITE_APP_PATH.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.basename(__FILE__);

return array_merge(include($parent), []);
