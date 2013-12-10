<?php
/**
 * ./app/components/web/widgets/RBaseWidget.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web\widgets\base;

use Yii;

use cascade\components\helpers\StringHelper;

use infinite\helpers\Html;

use yii\bootstrap\Nav;


abstract class EmbeddedWidget extends PanelWidget implements ObjectWidgetInterface {
	public $panelCssClass = 'embedded-panel';
}


?>