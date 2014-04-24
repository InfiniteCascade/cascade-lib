<?php
use yii\bootstrap\Nav;
use yii\helpers\Url;
use infinite\helpers\Html;

cascade\components\web\assetBundles\ObjectViewAsset::register($this);

$this->title = $object->descriptor;
if (!empty(Yii::$app->request->previousObject)) {
    $this->params['breadcrumbs'][] = ['label' => Yii::$app->request->previousObject->descriptor, 'url' => Yii::$app->request->previousObject->getUrl('view', [], false)];
}
$this->params['breadcrumbs'][] = $this->title;

Html::addCssClass($this->bodyHtmlOptions, 'double-top-nav');
$baseInstructions = [];
$baseInstructions['objectId'] = $object->primaryKey;
$refreshable = [
    'url' => Url::to('app/refresh'),
    'data' => ['baseInstructions' => $baseInstructions]
];
$this->bodyHtmlOptions['data-refreshable'] = json_encode($refreshable);
$js = [];
$sectionsMenu = [];
foreach ($sections as $section) {
    if ($section->priority === false) { continue; }
    $sectionsMenu[] = ['label' => $section->object->title, 'url' => '#section-'.$section->systemId];
}
$this->tinyMenu = $sectionsMenu;
echo Html::beginTag('div', ['class' => 'dashboard']);

$grid = Yii::createObject(['class' => 'infinite\\web\\grid\\Grid']);
$cells = [];
$mainColumnSize = 12;

if (isset($sections['_side'])) {
    $cellInner = $sections['_side']->object;
    $cellInner->htmlOptions['id'] = $cellInner->id;
    Html::addCssClass($cellInner->htmlOptions, 'ic-sidebar');
    $sideContent = $cellInner->generate();

    if (!empty($sideContent)) {
        $mainColumnSize -= 4;
        $js[] = '$("#'. $cellInner->id .'").cascadeAffix();';
        $js[] = "\$('body').scrollspy({ target: '.ic-sidenav', 'offset': 10 });";
        $cells[] = $sideCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $sideContent]);
        Yii::configure($sideCell, ['mediumDesktopColumns' => 4,'maxMediumDesktopColumns' => 4, 'largeDesktopSize' => false, 'tabletColumns' => 5]);
    }
}

if (count($sectionsMenu) > 2) {
    $mainColumnSize -= 2;
}

$mainCell = [];
foreach ($sections as $section) {
    if ($section->priority === false) { continue; }
    $mainCell[] = $section->object->cell;
}
$mainCellGrid = Yii::createObject(['class' => 'infinite\web\grid\Grid']);
$mainCellGrid->cells = $mainCell;
$cells[] = $mainCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $mainCellGrid->generate()]);
Yii::configure($mainCell,['mediumDesktopColumns' => 4, 'maxMediumDesktopColumns' => $mainColumnSize, 'largeDesktopSize' => false, 'tabletColumns' => $mainColumnSize + 1]);
Html::addCssClass($mainCell->htmlOptions, 'ic-main-cell');

if (count($sectionsMenu) > 2) {
    $js[] = '$(".ic-sidenav").cascadeAffix();';
    $menuContent = Html::beginTag('div', ['class' => 'ic-sidenav']);
    $menuContent .= Nav::widget([
            'options' => ['class' => 'navbar-default'],
            'encodeLabels' => false,
            'items' => $sectionsMenu,
        ]);;
    $menuContent .= Html::endTag('div');
    $cells[] = $menuCell = Yii::createObject(['class' => 'infinite\web\grid\Cell', 'content' => $menuContent]);
    Yii::configure($menuCell, ['mediumDesktopColumns' => 2, 'maxMediumDesktopColumns' => 2, 'largeDesktopSize' => false, 'tabletSize' => false]);
    Html::addCssClass($menuCell->htmlOptions, 'hidden-xs hidden-sm');
}

$grid->cells = $cells;
$grid->output();
echo Html::endTag('div'); // .dashboard
$this->registerJs(implode("\n", $js));
