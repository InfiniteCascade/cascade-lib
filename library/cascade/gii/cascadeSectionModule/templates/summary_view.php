<?php
echo "<?php\n"; ?>

$classSettings = array();
$classSettings['id'] = $this->widgetId .'-grid';
$classSettings['dataProvider'] = $items;
$classSettings['emptyText'] = 'No '. $this->Owner->title->plural .' found.';
$classSettings['widget'] = $this->widgetId;
$classSettings['state'] = $this->state;
$classSettings['limit'] = 10;
$classSettings['columns'] = array('link');
$classSettings['sortableAttributes'] = array(
			'familiarity' => 'Familiarity',
			'last_accessed' => 'Last Accessed');
$classSettings['views'] = array('grid');
$classSettings['currentView'] = 'grid';
$classSettings['additionalClasses'] = 'summary-widget';
// $classSettings['descriptor'] = $objectPrefix .'descriptor';
$templateContent = array(
	'link',
);

$classSettings['rendererSettings'] = array(
	'grid' => array(
		'templateContent' => $templateContent
	)
);

$this->widget('\cascade\components\web\widgets\grid\View', $classSettings);

<?php echo "?>"; ?>