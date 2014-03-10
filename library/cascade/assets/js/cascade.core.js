$preparer.add(function(context) {
	$("select[multiple]", context).selectpicker();
	$(".date", context).datepicker({
		autoclose: true,
		todayHighlight: true
	});
});

// side menu bar
$.fn.cascadeAffix = function (option) {
	var $self = $(this);
	var calculateBottom = function() {
		this.bottom = $('.footer').outerHeight(true);
		return this.bottom;
	};
	var calculateTop = function () {
		var offsetTop = $self.offset().top;
		var margin = parseInt($self.css('margin-top'), 10);
		var navOuterHeight = 0;
		$('nav.navbar-fixed-top').each(function() {
			navOuterHeight += $(this).outerHeight();
		});
		navOuterHeight += 10;
		this.top = offsetTop - navOuterHeight - margin;
		return this.top;
	};
	setTimeout(function() {
		$self.affix({offset: {top: calculateTop, bottom: calculateBottom}});
	}, 200);
};

// top menu bar
$(function() {
	
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