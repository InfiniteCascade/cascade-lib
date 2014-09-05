function simplifyFilterRules(rules) {
	if (rules.condition === undefined) { return {}; }
	var newRules = {'condition': rules.condition, 'rules': []};
	jQuery.each(rules.rules, function(index, rule) {
		if (rule.condition !== undefined) {
			var newRule = simplifyFilterRules(rule);
		} else {
			var newRule = {'field': rule.field, 'operator': rule.operator, 'value': rule.value};
		}
		newRules.rules.push(newRule);
	});
	return newRules;
}
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
			var rules = simplifyFilterRules($advancedFilter.queryBuilder('getRules'));
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
		//console.log(rules);
		return false;
	});
	$filterForm.submit();
});