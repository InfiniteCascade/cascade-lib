<?php
use Yii;

use infinite\helpers\Html;
use yii\bootstrap\Nav;
use infinite\web\bootstrap\SubNavBar;
use cascade\components\web\widgets\base\Section as SectionWidget;

Html::addCssClass($this->bodyHtmlOptions, 'double-top-nav');

$baseInstructions = [];
$baseInstructions['objectId'] = $object->primaryKey;
$refreshable = [
	'baseInstructions' => $baseInstructions,
	'url' => Html::url('app/refresh'),
	'data' => [Yii::$app->request->csrfVar => Yii::$app->request->csrfToken]
];
$this->bodyHtmlOptions['data-refreshable'] = json_encode($refreshable);
$js = [];
echo Html::beginTag('div', ['class' => 'dashboard']);
$navBar = SubNavBar::begin([
	'brandLabel' => $object->descriptor,
	'brandUrl' => $object->getUrl('view'),
	'options' => [
		'class' => 'navbar-fixed-top navbar-default',
	],
]);
SubNavBar::end();

$grid = Yii::createObject(['class' => 'infinite\web\grid\Grid']);
$cells = [];

$sectionsMenu = [];
foreach ($sections as $section) {
	if ($section->priority === false) { continue; }
	$sectionsMenu[] = ['label' => $section->sectionTitle, 'url' => '#section-'.$section->systemId];
}

$calculateBottom = 'function() {
	this.bottom = $(\'.footer\').outerHeight(true);
	return this.bottom;
}
';

if (count($sectionsMenu) > 2) {
	$calculateTop = 'function () {
			var offsetTop = $menuBar.offset().top;
			var sideBarMargin = parseInt($menuBar.children(0).css(\'margin-top\'), 10);
			var navOuterHeight = 0;
			$(\'nav.navbar-fixed-top\').each(function() {
				navOuterHeight += $(\'.navbar-header\').outerHeight();
			});
			console.log([offsetTop, navOuterHeight, sideBarMargin]);
			this.top = offsetTop - navOuterHeight - sideBarMargin;
			return this.top;
		}';

	$js[] = "\$('body').scrollspy({ target: '.ic-sidenav', 'offset': 80 });";

	$menuContent = Html::beginTag('div', ['class' => 'hidden-xs hidden-sm ic-sidenav']);
	$menuContent .= Nav::widget([
			'options' => ['class' => 'navbar-default'],
			'encodeLabels' => false,
			'items' => $sectionsMenu,
		]);;
	$menuContent .= Html::endTag('div');
	$cells[] = $menuCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $menuContent]);
	Yii::configure($menuCell, ['mediumDesktopColumns' => 2, 'maxMediumDesktopColumns' => 2, 'largeDesktopSize' => false, 'tabletSize' => false]);


	$js[] = '
	var $menuBar = $(".ic-sidenav");
	$menuBar.affix({offset: {top: '.$calculateTop.', bottom: '.$calculateBottom.'}});';
}


$mainCell = [];
foreach ($sections as $section) {
	if ($section->priority === false) { continue; }
	$mainCell[] = $section->object->cell;
}
$mainCellGrid = Yii::createObject(['class' => 'infinite\web\grid\Grid']);
$mainCellGrid->cells = $mainCell;
$cells[] = $mainCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $mainCellGrid->generate()]);
Yii::configure($mainCell,['mediumDesktopColumns' => 4, 'maxMediumDesktopColumns' => 8, 'largeDesktopSize' => false, 'tabletSize' => false]);

if (isset($sections['_side'])) {
	$cellInner = $sections['_side']->object;
	$cellInner->htmlOptions['id'] = $cellInner->id;

$calculateTop = 'function () {
			var offsetTop = $sideBar.offset().top;
			var sideBarMargin = parseInt($sideBar.children(0).css(\'margin-top\'), 10);
			var navOuterHeight = 0;
			$(\'nav.navbar-fixed-top\').each(function() {
				navOuterHeight += $(\'.navbar-header\').outerHeight();
			});
			// console.log([offsetTop, navOuterHeight, sideBarMargin]);
			this.top = offsetTop - navOuterHeight - sideBarMargin;
			return this.top;
		}
';

	$js[] = '
	setTimeout(function() {
		var $sideBar = $("#'. $cellInner->id .'");
		$sideBar.affix({offset: {top: '.$calculateTop.', bottom: '.$calculateBottom.'}});
	}, 100)';
	Html::addCssClass($cellInner->htmlOptions, 'ic-sidebar');

	// $cellInner->htmlOptions['data-spy'] = 'affix';
	// $cellInner->htmlOptions['data-offset-top'] = 120;
	// $cellInner->htmlOptions['data-offset-bottom'] = 400;
	// $cellInner->htmlOptions['data-spy'] = 'affix';

	$cells[] = $sideCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $cellInner->generate()]);
	Yii::configure($sideCell, ['mediumDesktopColumns' => 4,'maxMediumDesktopColumns' => 4, 'largeDesktopSize' => false, 'tabletSize' => false]);
}

$grid->cells = $cells;
$grid->output();
echo Html::endTag('div'); // .dashboard
$this->registerJs(implode("\n", $js));
?>