<?php
/**
 * ./protected/config/environments/production/client_script.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

return [];

return array(
	'class' => 'infiniteCore.components.web.RClientScript',
	'ignoreCompiled' => true,
	'scriptMap' => array(
		'jquery.js' => '/themes/ic/js/jquery-1.9.1.js',
		'jquery.min.js' => '/themes/ic/js/jquery-1.9.1.js',
		'jquery-ui.js' => '/themes/ic/js/jquery-ui-1.10.0.custom.js',
		'jquery-ui.min.js' => '/themes/ic/js/jquery-ui-1.10.0.custom.js',
		'jquery.ajaxSubmit.js' => '/themes/ic/js/jquery.ajaxSubmit.js',
		'jquery.slugInPlace.js' => '/themes/ic/js/jquery.slugInPlace.js',
		'jquery.foldable.js' => '/themes/ic/js/jquery.foldable.js',
		'jquery.tokeninput.js' => '/themes/ic/js/jquery-tokeninput/jquery.tokeninput.js',
		'jquery.pairedSelects.js' => '/themes/ic/js/jquery.pairedSelects.js',
		'jquery.objectSelector.js' => '/themes/ic/js/jquery.objectSelector.js',
		'jquery.quickfit.js' => '/themes/ic/js/jquery.quickfit.js',
		'jquery.widgets.js' => '/themes/ic/js/jquery.widgets.js',
		'jquery.poshytip.min.js' => '/themes/ic/js/jquery.poshytip.min.js',
		'jquery.appeared.js' => '/themes/ic/js/jquery.appeared.js',
		'jquery.listView.js' => '/themes/ic/js/jquery.listView.js',
		'jquery.gridView.js' => '/themes/ic/js/jquery.gridView.js',
		'jquery.sections.js' => '/themes/ic/js/jquery.sections.js',
		'jquery.relationBuilder.js' => '/themes/ic/js/jquery.relationBuilder.js',
		'jquery.tinymce.js' => '/themes/ic/js/tiny_mce/jquery.tinymce.js',
		'jquery.notify.js' => '/themes/ic/js/jquery-notify/src/jquery.notify.js',
		'jquery.smartDialog.js' => '/themes/ic/js/jquery-smartDialog/src/jquery.smartDialog.js',
		'jquery.idleNotice.js' => '/themes/ic/js/jquery-idleNotice/src/jquery.idleNotice.js',

		'tinymce.js' => '/themes/ic/js/tiny_mce/jquery.tinymce.js',
		'underscore.js' => '/themes/ic/js/underscore.min.js',
		'bootstrap.js' => '/themes/ic/js/bootstrap.js',
		'common.js' => '/themes/ic/js/common.js',
		'IE8.js' => '/themes/ic/js/ie7/IE8.js',
		'IE9.js' => '/themes/ic/js/ie7/IE9.js',
		'google.js' => 'http://www.google.com/jsapi',

		//'tinymce.css' => '/themes/ic/js/tinymce/',
		'jquery.multiselect.js' => '/themes/ic/js/jquery.multiselect.js',
		'jquery.colorpicker.css' => '/themes/ic/js/jquery-colorpicker/css/colorpicker.css',
		'main.css' => '/themes/ic/css/main.css',
		'icons.css' => '/themes/ic/css/icons.css',
		'print.css' => '/themes/ic/css/print.css',
		'jquery.tokeninput.css' => '/themes/ic/js/jquery-tokeninput/jquery.tokeninput.css',
		'form.css' => '/themes/ic/css/form.css',
		'ui.notify.css' => '/themes/ic/js/jquery-notify/ui.notify.css',

		'jquery-ui.css' => '/themes/ic/css/jquery-ui/jquery-ui-1.10.0.custom.css',
	),
	'scriptGroups' => array(
		'javascript' => array(
			'merged' => array(
				'jquery.js', 'jquery-ui.js', 
				'bootstrap.js',
				'jquery.ajaxSubmit.js', 'jquery.relationBuilder.js', 'jquery.tinymce.js', 'jquery.tokeninput.js', 
				'jquery.appeared.js', 'jquery.idleNotice.js', 'jquery.smartDialog.js', 'jquery.notify.js',
				'jquery.slugInPlace.js', 'jquery.pairedSelects.js', 'jquery.multiselect.js', 'jquery.widgets.js', 
				'jquery.listView.js', 'jquery.gridView.js', 'jquery.sections.js', 'underscore.js', 
				'sceditor.js',
				'common.js',
			),
		),
		'css' => array(
			'merged' => array(
				'form.css', 'jquery-ui.css', 'ui.notify.css', 'main.css', 'colors.css', 'icons.css', 'sceditor.css',
			),
		),



	),
);
?>
