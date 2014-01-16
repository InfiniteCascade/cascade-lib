$preparer.add(function(context) {
	$("select[multiple]", context).selectpicker();
	$(".date", context).datepicker({
		autoclose: true,
		todayHighlight: true
	});
});

// basic template engine used in typeahead.js
// from https://github.com/twitter/typeahead.js/issues/14
var SingleTemplateEngine = {
    compile: function(template) {
        return {
            render: function(context) {
                return template.replace(/\{\{(\w+)\}\}/g,
				    function(match, p1) {
				         return jQuery('<div/>').text(context[p1] || '').html();
				    });
			}
        };
    }
};