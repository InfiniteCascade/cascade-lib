var datePickerOptions = {
    autoclose: true,
    todayHighlight: true
};

function prepareCascadeFormFields(context) {
    $('select[multiple]', context).selectpicker();
    $('.date', context).datepicker(datePickerOptions);
}

$preparer.add(function(context) {
    $(context).timeago({selector: 'time.relative-time'});
    prepareCascadeFormFields(context);
    $('.refreshable.widget-lazy', context).refreshable();

    $('#searchform-query', context).objectSearch({
        'data': {
            'typeFilters': ['dashboard']
        },
        'resultsBox': {
            'maxWidth': 350,
            'oriented': 'left'
        },
        'callback': function(object, datum) {
            window.location = datum.url;
        }
    });
});

// side menu bar
$.fn.cascadeAffix = $.fn.canisAffix;

// basic template engine used in typeahead.js
// from https://github.com/twitter/typeahead.js/issues/14
var SingleTemplateEngine = {
    compile: function(template) {
        return function(context) {
            return template.replace(/\{\{(\w+)\}\}/g,
function(match, p1) {
    if (typeof context[p1] === 'object' && Array.isArray(context[p1])) {
        context[p1] = context[p1].join('<br />');
    }
    return jQuery('<div/>').html(context[p1] || '').html();
				}
			);
        };
    }
};
