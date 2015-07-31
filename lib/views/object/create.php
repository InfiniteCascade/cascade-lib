<?php
/**
 * ./app/views/app/create.php.
 *
 * @author Jacob Morrison <jacob@canis.io>
 */
use canis\helpers\Html;
cascade\components\web\assetBundles\ObjectViewAsset::register($this);
$form->output();

if (!empty($primaryModel->modified)) {
    echo Html::beginTag('div', ['class' => 'canis-info-group']);
    echo Html::tag('div', 'Modified', ['class' => 'canis-info-label']);
    echo Html::tag('div', $primaryModel->modified, ['class' => 'canis-info-label']);
    echo Html::endTag('div');
}
