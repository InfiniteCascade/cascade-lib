<?php
/**
 * ./app/config/environments/common/modules.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


return [
	'WidgetWatching' => [
	    'class' => 'cascade\modules\WidgetWatching\Module',
	],
	'TypeAccount' => [
	    'class' => 'cascade\modules\TypeAccount\Module',
	    'title' => 'Organizations'
	],
	'TypeIndividual' => [
	    'class' => 'cascade\modules\TypeIndividual\Module',
	],
	'TypePostalAddress' => [
	    'class' => 'cascade\modules\TypePostalAddress\Module',
	],
	'TypePhoneNumber' => [
		'class' => 'cascade\modules\TypePhoneNumber\Module',
    ],
	'TypeEmailAddress' => [
		'class' => 'cascade\modules\TypeEmailAddress\Module',
    ],
    'TypeWebAddress' => [
        'class' => 'cascade\modules\TypeWebAddress\Module',
    ],
];
?>
