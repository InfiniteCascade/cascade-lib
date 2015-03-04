<?php
use yii\helpers\Html;

/*
 * @var $this \infinite\base\View
 * @var $content string
 */
cascade\components\web\assetBundles\AppAsset::register($this);
Yii::$app->collectors['themes']->registerAssetBundles($this);

if (YII_ENV_DEV) {
    Html::addCssClass($this->bodyHtmlOptions, 'development');
}

$post = [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken];
$this->bodyHtmlOptions['data-post'] = json_encode($post);
$this->bodyHtmlOptions['data-cascade-types'] = json_encode(Yii::$app->collectors['types']->pageMeta);

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?=Yii::$app->charset; ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php
    if (!empty($this->title)) {
        echo Html::encode(strip_tags($this->title)).' &mdash; ';
    }
    echo Html::encode(Yii::$app->params['siteName']);
    ?></title>
    <?php $this->head(); ?>
</head>
<?= Html::beginTag('body', $this->bodyHtmlOptions); ?>
<?php
echo Html::beginTag('div', ['class' => 'main-container container-fluid']);
$this->beginBody();
echo $content;
$this->endBody();
?>
</body>
</html>
<?php $this->endPage(); ?>
