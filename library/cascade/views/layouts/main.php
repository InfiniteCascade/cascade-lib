<?php
use cascade\components\web\bootstrap\Nav;
use cascade\components\web\bootstrap\TopNavBar;
use cascade\models\SearchForm;
use infinite\deferred\widgets\NavItem as DeferredNavItem;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

cascade\components\web\assetBundles\AppAsset::register($this);

$this->beginContent('@cascade/views/layouts/frame.php');
$themeEngine = Yii::$app->collectors['themes'];
$identity = $themeEngine->getIdentity($this);

$itemTypes = [];
foreach (Yii::$app->collectors['types']->getAll() as $type) {
    if (empty($type->object)) {
        continue;
    }
    if (empty($type->object->hasDashboard)) {
        continue;
    }
    $itemTypes[$type->object->title . '-' . $type->systemId] = ['label' => $type->object->title->upperPlural, 'url' => ['/object/browse', 'type' => $type->systemId]];
}
ksort($itemTypes);
$itemTypes = array_values($itemTypes);

TopNavBar::begin([
    //'brandLabel' => Html::img($identity->getLogo(['height' => 35])),
    'options' => [
        'class' => 'ic-navbar-top navbar-default navbar-fixed-top',
    ],
]);
if (!Yii::$app->user->isGuest) {
    $browseLabel = 'Browse';
    if (isset(Yii::$app->request->object)) {
        $browseLabel .= ' ' . Html::tag('span', Yii::$app->request->object->objectType->title->upperPlural, ['class' => 'object-type']);
    }
    $topMenu = [];
    $topMenu[] = [
        'label' =>  '<span class="icon fa fa-home"></span> <span class="nav-label hidden-xs hidden-sm">Dashboard</span>',
        'url' => ['/app/index'],
    ];
    $topMenu[] = [
        'label' =>  '<span class="icon fa fa-th"></span> <span class="nav-label hidden-xs hidden-sm">' . $browseLabel . '</span>',
        'url' => ['/object/index'],
        'items' => $itemTypes,
        'active' => function ($nav, $item) {
            $check = ['/^\/object\//']; // , '/^object\/view/', '/^object\/index/', '/^object\/browse/'
            foreach ($check as $c) {
                if (preg_match($c, $nav->route) === 1) {
                    return true;
                }
            }

            return false;
        },
    ];
    $reports = Yii::$app->collectors['reports']->getAllActive();
    if (!empty($reports)) {
        $topMenu[] = [
            'label' =>  '<span class="icon fa fa-filter"></span> <span class="nav-label hidden-xs hidden-sm">Reports</span>',
            'url' => ['/report'],
        ];
    }
    $tools = Yii::$app->collectors['tools']->getAllActive();
    if (!empty($tools)) {
        $topMenu[] = [
            'label' =>  '<span class="icon fa fa-wrench"></span> <span class="nav-label hidden-xs hidden-sm">Tools</span>',
            'url' => ['/tool'],
        ];
    }

    if (Yii::$app->gk->is('administrators')) {
        $topMenu[] = ['label' => '<span class="icon fa fa-cogs"></span> <span class="nav-label hidden-xs hidden-sm">Administration</span>', 'activeChildren' => '/admin', 'url' => ['/admin/dashboard']];
    }
    //$topMenu[] = ['label' =>  '<span class="glyphicon glyphicon-home"></span> <span class="nav-label hidden-xs hidden-sm">Dashboard</span>', 'url' => ['/']];

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav pull-left'],
        'encodeLabels' => false,
        'items' => $topMenu,
    ]);
}

$identityLink = isset(Yii::$app->user->identity) ? Yii::$app->user->identity->url : false;
$userMenu = [];
$userMenu[] = DeferredNavItem::widget([]);
if (Yii::$app->user->isGuest) {
    $userMenu[] = ['label' => 'Sign In', 'url' => ['/app/login'],
                    'linkOptions' => ['data-method' => 'post'], ];
} else {
    $userMenuItem = [
        'label' =>  '<span class="glyphicon glyphicon-user"></span> <span class="nav-label hidden-xs hidden-sm">' . Yii::$app->user->identity->first_name . '</span>',
        'url' => '#',
        'linkOptions' => [],
        'items' => [],
    ];
    $userMenuItem['items'][] = [
        'label' => 'Profile' ,
        'url' => $identityLink,
        'linkOptions' => ['title' => 'Profile'],
    ];
    $userMenuItem['items'][] = [
        'label' => 'Logout' ,
        'url' => ['/app/logout'],
        'linkOptions' => ['data-method' => 'post', 'title' => 'Logout'],
    ];
    $userMenu[] = $userMenuItem;
}
echo Nav::widget([
    'options' => ['class' => 'navbar-nav pull-right'],
    'encodeLabels' => false,
    'items' => $userMenu,
]);
if (!Yii::$app->user->isGuest) {
    $searchModel = new SearchForm();
    $searchForm = ActiveForm::begin([
        'id' => 'search-form',
        'enableClientValidation' => false,
        'options' => ['class' => 'navbar-form pull-right', 'role' => 'search'],
    ]);
    echo $searchForm->field($searchModel, 'query',
        [
            'inputOptions' => ['placeholder' => 'Search', 'class' => 'form-control'],
            'template' => '{input}',
        ]);
    echo Html::submitButton('Search', ['class' => 'btn btn-default sr-only']);
    ActiveForm::end();
}
TopNavBar::end();
?>

<div class="inner-container container-fluid">
<?=Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    'encodeLabels' => false,
]); ?>
<?php
    if (($success = Yii::$app->session->getFlash('success', false, true))) {
        echo Html::tag('div', $success, ['class' => 'alert alert-success']);
    }
    if (($error = Yii::$app->session->getFlash('error', false, true))) {
        echo Html::tag('div', $error, ['class' => 'alert alert-danger']);
    }
?>
<?= $content; ?>
</div>

<footer class="footer">
<div class="container-fluid">
<!--            <p class="pull-left">&copy; <?=Yii::$app->name?> <?=date('Y'); ?></p>
    <p class="pull-right"><?=Yii::powered(); ?></p> -->
</div>
</footer>

<?php
echo Html::endTag('div');
$this->endContent();
