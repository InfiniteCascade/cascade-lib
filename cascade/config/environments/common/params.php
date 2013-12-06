<?php
/**
 * ./app/config/environments/common/params.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

return array(
	'siteName' => 'Cascade',
	'sessionExpiration' => 3600,
	'defaultCountry' => 'US',
	'defaultSubnationalDivision' => null,
	'migrationAliases' => ['@cascade/migrations'],
	// site look
	'logoLogin' => "/themes/ic/img/cascade-logo-450.png",
	'logoSmall' => "/themes/ic/img/cascade-logo-75.png"
);
?>
