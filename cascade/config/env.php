<?php
if (!defined('YII_DEBUG')) 				{	define('YII_DEBUG', true);				}
if (!defined('YII_TRACE_LEVEL')) 		{	define('YII_TRACE_LEVEL', 3);			}
if (!defined('YII_ENV')) 				{	define('YII_ENV', 'dev');	}

if (!defined('INFINITE_APP_INSTANCE_VERSION')) 	{	define('INFINITE_APP_INSTANCE_VERSION', 0); 			}
if (!defined('INFINITE_APP_ENVIRONMENT')) 		{	define('INFINITE_APP_ENVIRONMENT', 'development'); 			}
if (!defined('INFINITE_APP_ENVIRONMENT_PATH')) 	{	define('INFINITE_APP_ENVIRONMENT_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'environments' . DIRECTORY_SEPARATOR . 'setups' . DIRECTORY_SEPARATOR . 'development'); 			}
if (!defined('INFINITE_APP_DATABASE_HOST')) 		{	define('INFINITE_APP_DATABASE_HOST', '127.0.0.1'); 		}
if (!defined('INFINITE_APP_DATABASE_PORT')) 		{	define('INFINITE_APP_DATABASE_PORT', '3306'); 		}
if (!defined('INFINITE_APP_DATABASE_USERNAME')) 	{	define('INFINITE_APP_DATABASE_USERNAME', 'root'); 	}
if (!defined('INFINITE_APP_DATABASE_PASSWORD')) 	{	define('INFINITE_APP_DATABASE_PASSWORD', 'root'); 	}
if (!defined('INFINITE_APP_DATABASE_DBNAME')) 	{	define('INFINITE_APP_DATABASE_DBNAME', 'cascade'); 		}
?>