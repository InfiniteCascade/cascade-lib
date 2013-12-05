<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/**
 * @var $this \infinite\base\View
 * @var $content string
 */
cascade\components\web\assetBundles\AppAsset::register($this);

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
<?php $this->beginBody(); ?>
	<?php
		NavBar::begin([
			'brandLabel' => Yii::$app->name,
			'brandUrl' => Yii::$app->homeUrl,
			'options' => [
				'class' => 'i-navbar-top navbar-inverse navbar-fixed-top',
			],
		]);
		echo Nav::widget([
			'options' => ['class' => 'navbar-nav pull-right'],
			'encodeLabels' => false,
			'items' => [
				['label' => 'Home', 'url' => ['/app/index']],
				Yii::$app->user->isGuest ?
					['label' => 'Sign In', 'url' => ['/app/login'],
						'linkOptions' => ['data-method' => 'post']] :
					['label' => '<span class="glyphicon glyphicon-off"></span> (' . Yii::$app->user->identity->username .')' ,
						'url' => ['/app/logout'],
						'linkOptions' => ['data-method' => 'post']],
			],
		]);
		NavBar::end();
	?>

	<div class="container">
		<?=Breadcrumbs::widget([
			'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
		]); ?>
		<?=$content; ?>
	</div>

	<footer class="footer">
		<div class="container">
<!-- 			<p class="pull-left">&copy; <?=Yii::$app->name?> <?=date('Y'); ?></p>
			<p class="pull-right"><?=Yii::powered(); ?></p> -->
		</div>
	</footer>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
