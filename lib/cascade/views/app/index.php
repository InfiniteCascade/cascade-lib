<?php
/**
 * @var yii\base\View $this
 */
$this->title = 'My Yii Application';
echo "<br /><br /><br /><br />";
foreach (Yii::$app->collectors['widgets']->bucket as $item) {
	var_dump($item->systemId);
}
?>