<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use cascade\components\web\bootstrap\NavBar;

/**
 * @var $this \infinite\base\View
 * @var $content string
 */
cascade\components\web\assetBundles\AppAsset::register($this);
Yii::$app->collectors['themes']->registerAssetBundles($this);

if (YII_ENV_DEV) {
	Html::addCssClass($this->bodyHtmlOptions, 'development');
}
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
		NavBar::begin([
			'brandLabel' => 'Dashboard',//Yii::$app->name,
			'brandUrl' => Yii::$app->homeUrl,
			'options' => [
				'class' => 'i-navbar-top navbar-inverse navbar-fixed-top',
			],
		]);
		$identityLink = isset(Yii::$app->user->identity) ? Yii::$app->user->identity->url : false;
		$topItems = [];
		if (Yii::$app->user->isGuest) {
			$topItems[] = ['label' => 'Sign In', 'url' => ['/app/login'],
							'linkOptions' => ['data-method' => 'post']];
		} else {
			$topItems[] = ['label' =>  Yii::$app->user->identity->first_name, 'url' => $identityLink];
			$topItems[] = ['label' => '<span class="glyphicon glyphicon-off"></span>' ,
								'url' => ['/app/logout'],
								'linkOptions' => ['data-method' => 'post']];
		}
		echo Nav::widget([
			'options' => ['class' => 'navbar-nav navbar-right'],
			'encodeLabels' => false,
			'items' => $topItems,
		]);
		NavBar::end();
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
