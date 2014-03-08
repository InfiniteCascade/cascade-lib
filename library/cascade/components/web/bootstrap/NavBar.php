<?php
namespace cascade\components\web\bootstrap;

use Yii;
use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\BootstrapPluginAsset;

/**
 * NavBar renders a navbar HTML component.
 *
 * Any content enclosed between the [[begin()]] and [[end()]] calls of NavBar
 * is treated as the content of the navbar. You may use widgets such as [[Nav]]
 * or [[\yii\widgets\Menu]] to build up such content. For example,
 *
 * ```php
 * use yii\bootstrap\NavBar;
 * use yii\widgets\Menu;
 *
 * NavBar::begin(['brandLabel' => 'NavBar Test']);
 * echo Nav::widget([
 *     'items' => [
 *         ['label' => 'Home', 'url' => ['/site/index']],
 *         ['label' => 'About', 'url' => ['/site/about']],
 *     ],
 * ]);
 * NavBar::end();
 * ```
 *
 * @see http://getbootstrap.com/components/#navbar
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Jacob Morrison <jomorrison@gmail.com>
 * @since 2.0
 */

class NavBar extends \yii\bootstrap\Widget {
	/**
	 * @var array the HTML attributes for the widget container tag. The following special options are recognized:
	 *
	 * - tag: string, defaults to "nav", the name of the container tag
	 */
	public $options = [];
	/**
	 * @var array the HTML attributes for the container tag. The following special options are recognized:
	 *
	 * - tag: string, defaults to "div", the name of the container tag
	 */
	public $containerOptions = [];
	/**
	 * @var string the text of the brand. Note that this is not HTML-encoded.
	 * @see http://getbootstrap.com/components/#navbar
	 */
	public $brandLabel;
	/**
	 * @param array|string $url the URL for the brand's hyperlink tag. This parameter will be processed by [[Html::url()]]
	 * and will be used for the "href" attribute of the brand link. If not set, [[\yii\web\Application::homeUrl]] will be used.
	 */
	public $brandUrl;
	/**
	 * @var string text to show for screen readers for the button to toggle the navbar.
	 */
	public $screenReaderToggleText = 'Toggle navigation';
	/**
	 * @var bool whether the navbar content should be included in an inner div container which by default
	 * adds left and right padding. Set this to false for a 100% width navbar.
	 */
	public $renderInnerContainer = true;
	/**
	 * @var array the HTML attributes of the inner container.
	 */
	public $innerContainerOptions = [];

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$themeEngine = Yii::$app->collectors['themes'];
		$identity = $themeEngine->getIdentity($this->view);
		$this->clientOptions = false;
		Html::addCssClass($this->options, 'navbar');
		if ($this->options['class'] === 'navbar') {
			Html::addCssClass($this->options, 'navbar-default');
		}
		
		$breadcrumbs = Breadcrumbs::widget([
			'homeLink' => ['label' => $this->brandLabel, 'url' => $this->brandUrl],
			'options' => ['class' => 'breadcrumb navbar-brand'],
			'links' => isset($this->view->params['breadcrumbs']) ? $this->view->params['breadcrumbs'] : [],
		]);
		if ($breadcrumbs === '') {
			$breadcrumbs = '<ul class="breadcrumb navbar-brand"><li><img src="'.$identity->getLogo(['height' => 30]).'" /></li></ul>';
		}
		$options = $this->options;
		$tag = ArrayHelper::remove($options, 'tag', 'nav');
		echo Html::beginTag($tag, $options);
		if ($this->renderInnerContainer) {
			if (!isset($this->innerContainerOptions['class'])) {
				Html::addCssClass($this->innerContainerOptions, 'container');
			}
			echo Html::beginTag('div', $this->innerContainerOptions);
		}
		echo Html::beginTag('div', ['class' => 'navbar-header']);
		if (!isset($this->containerOptions['id'])) {
			$this->containerOptions['id'] = "{$this->options['id']}-collapse";
		}
		echo $this->renderToggleButton();
		echo $breadcrumbs;
		echo Html::endTag('div');
		Html::addCssClass($this->containerOptions, 'collapse');
		Html::addCssClass($this->containerOptions, 'navbar-collapse');
		$options = $this->containerOptions;
		$tag = ArrayHelper::remove($options, 'tag', 'div');
		echo Html::beginTag($tag, $options);
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$tag = ArrayHelper::remove($this->containerOptions, 'tag', 'div');
		echo Html::endTag($tag);
		if ($this->renderInnerContainer) {
			echo Html::endTag('div');
		}
		$tag = ArrayHelper::remove($this->options, 'tag', 'nav');
		echo Html::endTag($tag, $this->options);
		BootstrapPluginAsset::register($this->getView());
	}

	/**
	 * Renders collapsible toggle button.
	 * @return string the rendering toggle button.
	 */
	protected function renderToggleButton()
	{
		$bar = Html::tag('span', '', ['class' => 'icon-bar']);
		$screenReader = "<span class=\"sr-only\">{$this->screenReaderToggleText}</span>";
		return Html::button("{$screenReader}\n{$bar}\n{$bar}\n{$bar}", [
			'class' => 'navbar-toggle',
			'data-toggle' => 'collapse',
			'data-target' => "#{$this->containerOptions['id']}",
		]);
	}
}
?>