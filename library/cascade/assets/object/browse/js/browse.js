$(function() {
	var $advancedFilter = $("#advanced-filter-builder");
	var $simpleFilter = $("#simple-filter-input");
	var $filterForm = $("#filter-form");
	console.log($filterForm);
	$filterForm.submit(function() {
		var data = {};
		if ($("#simple-filter").hasClass('active')) {
			data.query = $simpleFilter.val();
		} else {
			var rules = $advancedFilter.queryBuilder('getRules');
			data.advancedQuery = JSON.stringify(rules);
		}
		console.log(data);
		return false;
	});
});