<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use cascade\components\web\bootstrap\TopNavBar;
use cascade\models\SearchForm;
use yii\widgets\ActiveForm;

/**
 * @var $this \infinite\base\View
 * @var $content string
 */
cascade\components\web\assetBundles\AppAsset::register($this);
Yii::$app->collectors['themes']->registerAssetBundles($this);

if (YII_ENV_DEV) {
	Html::addCssClass($this->bodyHtmlOptions, 'development');
}

$itemTypes = [];
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="<?=Yii::$app->charset; ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=Html::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>
<?= Html::beginTag('body', $this->bodyHtmlOptions); ?>
<?php 
echo Html::beginTag('div', ['class' => 'main-container container']);
$this->beginBody(); ?>
	<?php
		$themeEngine = Yii::$app->collectors['themes'];
		$identity = $themeEngine->getIdentity($this);

		TopNavBar::begin([
			//'brandLabel' => Html::img($identity->getLogo(['height' => 35])),
			'options' => [
				'class' => 'ic-navbar-top navbar-default navbar-fixed-top',
			],
		]);
		if (!Yii::$app->user->isGuest) {
			$topMenu = [];
			$topMenu[] = ['label' =>  '<span class="glyphicon glyphicon-home"></span> <span class="hidden-xs hidden-sm">Dashboard</span>', 'url' => ['/']];
			$topMenu[] = ['label' =>  '<span class="glyphicon glyphicon-th"></span> <span class="hidden-xs hidden-sm">Browse</span>', 'url' => ['object/browse'], 'items' => $itemTypes];
			$topMenu[] = ['label' =>  '<span class="glyphicon glyphicon-filter"></span> <span class="hidden-xs hidden-sm">Reports</span>', 'url' => ['reports/index']];
			//$topMenu[] = ['label' =>  '<span class="glyphicon glyphicon-home"></span> <span class="hidden-xs hidden-sm">Dashboard</span>', 'url' => ['/']];

			echo Nav::widget([
				'options' => ['class' => 'navbar-nav pull-left'],
				'encodeLabels' => false,
				'items' => $topMenu,
			]);


		}


		$identityLink = isset(Yii::$app->user->identity) ? Yii::$app->user->identity->url : false;
		$userMenu = [];
		if (Yii::$app->user->isGuest) {
			$userMenu[] = ['label' => 'Sign In', 'url' => ['/app/login'],
							'linkOptions' => ['data-method' => 'post']];
		} else {
			$userMenuItem = [
				'label' =>  '<span class="glyphicon glyphicon-user"></span> <span class="hidden-xs hidden-sm">' . Yii::$app->user->identity->first_name .'</span>', 
				'url' => '#',
				'linkOptions' => [],
				'items' => []
			];
			$userMenuItem['items'][] = [
				'label' => 'Profile' ,
				'url' => $identityLink,
				'linkOptions' => ['title' => 'Profile']
			];
			$userMenuItem['items'][] = [
				'label' => 'Logout' ,
				'url' => ['/app/logout'],
				'linkOptions' => ['data-method' => 'post', 'title' => 'Logout']
			];
			$userMenu[] = $userMenuItem;
		}
		echo Nav::widget([
			'options' => ['class' => 'navbar-nav pull-right'],
			'encodeLabels' => false,
			'items' => $userMenu,
		]);
		if (!Yii::$app->user->isGuest) {
			$searchModel = new SearchForm;
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

	<div class="inner-container container">
		<?=$content; ?>
	</div>

	<footer class="footer">
		<div class="container">
<!-- 			<p class="pull-left">&copy; <?=Yii::$app->name?> <?=date('Y'); ?></p>
			<p class="pull-right"><?=Yii::powered(); ?></p> -->
		</div>
	</footer>

<?php 
echo Html::endTag('div');
$this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
