<?php
use Yii;

use infinite\helpers\Html;
use yii\bootstrap\Nav;
use infinite\web\bootstrap\SubNavBar;

$baseInstructions = [];
$baseInstructions['objectId'] = $object->primaryKey;
$refreshable = [
	'baseInstructions' => $baseInstructions,
	'url' => Html::url('app/refresh'),
	'data' => [Yii::$app->request->csrfVar => Yii::$app->request->csrfToken]
];
$this->bodyHtmlOptions['data-refreshable'] = json_encode($refreshable);
$js = [];
$js[] = "\$('body').scrollspy({ target: '#object-dashboard-navbar', 'offset': 100 });";
echo Html::beginTag('div', ['class' => 'dashboard']);
$navBar = SubNavBar::begin([
	'brandLabel' => $object->descriptor,
	'brandUrl' => $object->getUrl('view'),
	'options' => [
		'class' => 'navbar-fixed-top',
	],
]);
$sectionsMenu = [];
foreach ($sections as $section) {
	if (substr($section->systemId, 0, 1) === '_') { continue; }

	$widgets = $section->object->widget->widgets;
	if (count($widgets) === 1)
	{
		$firstWidget = array_shift($widgets);
		$sectionsMenu[] = ['label' => $firstWidget->object->object->panelTitle, 'url' => '#section-'.$section->systemId];
	} else {
		$sectionsMenu[] = ['label' => $section->object->sectionTitle, 'url' => '#section-'.$section->systemId];
	}
}
echo Html::beginTag('div', ['id' => 'object-dashboard-navbar']);
echo Nav::widget([
	'options' => ['class' => 'navbar-nav navbar-right'],
	'items' => $sectionsMenu,
]);
echo Html::endTag('div');
SubNavBar::end();

$grid = Yii::createObject(['class' => 'infinite\web\grid\Grid']);
$cells = [];

if (isset($sections['_side'])) {
	$cellInner = $sections['_side']->object->widget;
	$cellInner->htmlOptions['id'] = $cellInner->id;
	$js[] = '
	var $sideBar = $("#'. $cellInner->id .'");
	$sideBar.affix({
		\'top\': function () {
			var offsetTop = $sideBar.offset().top;
			var sideBarMargin = parseInt($sideBar.children(0).css(\'margin-top\'), 10);
			var navOuterHeight = $(\'.navbar-header\').height() + $(\'#object-dashboard-navbar\').height();
			return (this.top = offsetTop - navOuterHeight - sideBarMargin);
		},
		\'bottom\': function() {
			return (this.bottom = $(\'.footer\').outerHeight(true));
		}
	});';
	//$cellInner->htmlOptions['data-spy'] = 'affix';
	//$cellInner->htmlOptions['data-offset-top'] = 5;
	// $cellInner->htmlOptions['data-offset-bottom'] = 50;
	// $cellInner->htmlOptions['data-parent-height-watch'] = true;
	// $cellInner->htmlOptions['data-parent-height-watch-ancestor'] = '.row';
	Html::addCssClass($cellInner->htmlOptions, 'ic-sidebar');

	$cells[] = $sideCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $cellInner->generate()]);
	Yii::configure($sideCell, ['mediumDesktopColumns' => 4, 'largeDesktopSize' => false, 'tabletSize' => false]);
}

$mainCell = [];
foreach ($sections as $section) {
	if (substr($section->systemId, 0, 1) === '_') { continue; }
	$mainCell[] = $section->object->generate() .'<!--end '.$section->systemId.'-->';
}
$cells[] = $mainCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => implode('', $mainCell)]);
Yii::configure($mainCell,['mediumDesktopColumns' => 8, 'largeDesktopSize' => false, 'tabletSize' => false]);

$grid->cells = $cells;
$grid->output();
echo Html::endTag('div'); // .dashboard
$this->registerJs(implode("\n", $js));
?>