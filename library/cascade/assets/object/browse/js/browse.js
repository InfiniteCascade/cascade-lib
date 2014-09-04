$(function() {
	var $advancedFilter = $("#advanced-filter-builder");
	var $simpleFilter = $("#simple-filter-input");
	var $filterForm = $("#filter-form");
	var currentRestDraw;
	$filterForm.submit(function() {
		var data = {};
		if ($("#simple-filter").hasClass('active')) {
			data.query = $simpleFilter.val();
		} else {
			var rules = $advancedFilter.queryBuilder('getRules');
			data.advancedQuery = JSON.stringify(rules);
		}
		if (currentRestDraw) {
			currentRestDraw.destroy();
		}
		request = {
			'url': $(this).attr('action'),
			'data': data
		};
		currentRestDraw = new RestDraw($("#filter-results"), request);
		return false;
	});
	$filterForm.submit();
});