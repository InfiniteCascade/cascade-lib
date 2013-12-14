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
    'TypeUser' => [
        'class' => 'cascade\modules\TypeUser\Module',
    ],
    'TypeGroup' => [
        'class' => 'cascade\modules\TypeGroup\Module',
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
    'TypeFile' => [
        'class' => 'cascade\modules\TypeFile\Module',
    ],
    'TypeNote' => [
        'class' => 'cascade\modules\TypeNote\Module',
    ],
    'TypeProject' => [
        'class' => 'cascade\modules\TypeProject\Module',
    ],
    'TypeTaskSet' => [
        'class' => 'cascade\modules\TypeTaskSet\Module',
    ],
    'TypeTime' => [
        'class' => 'cascade\modules\TypeTime\Module',
    ],
];
?>
