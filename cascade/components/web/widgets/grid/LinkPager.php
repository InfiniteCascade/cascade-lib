<?php
/**
 * ./app/components/web/widgets/core/RLinkPager.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web\widgets\grid;

use \infinite\helpers\Html;


class LinkPager extends \yii\widgets\LinkPager {
	protected $_state;

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getState() {
		return $this->_state;
	}


	/**
	 *
	 *
	 * @param unknown $state
	 */
	public function setState($state) {
		$this->_state = $state;
	}

	/**
	 * Renders a page button.
	 * You may override this method to customize the generation of page buttons.
	 * @param string $label the text label for the button
	 * @param integer $page the page number
	 * @param string $class the CSS class for the page button.
	 * @param boolean $disabled whether this page button is disabled
	 * @param boolean $active whether this page button is active
	 * @return string the rendering result
	 */
	protected function renderPageButton($label, $page, $class, $disabled, $active)
	{
		if ($active) {
			$class .= ' ' . $this->activePageCssClass;
		}
		if ($disabled) {
			$class .= ' ' . $this->disabledPageCssClass;
		}
		$state = $this->getState();
		$state['page'] = $page + 1;
		$jsonState = json_encode($state);

		$class .= ' stateful';
		$class = trim($class);
		$options = ['data-state' => $jsonState, 'class' => $class === '' ? null : $class];
		return Html::tag('li', Html::a($label, $this->pagination->createUrl($page), ['data-page' => $page]), $options);
	}

}


?>
