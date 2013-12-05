$(document).on('refresh.cascade-api', '.refreshable', function(e, data) {
	var settings = {};
	var instructions = {};
	if (settings.data === undefined) {
		settings.data = {};
	}
    if (typeof data === 'object') {
    	settings.data = jQuery.extend(true, settings.data, data);
    }
	if (typeof $('body').data('refreshable') === 'object' ) {
		settings = jQuery.extend(true, settings, $('body').data('refreshable'));
	}
	if (typeof settings.baseInstructions === 'object') {
		instructions = jQuery.extend(true, instructions, settings.baseInstructions);
	}
	delete settings.baseInstructions;
	if (typeof $(this).data('instructions') === 'object' ) {
		instructions = jQuery.extend(true, instructions, $(this).data('instructions'));
	}
	
	settings.dataType = 'json';
	settings.type = 'POST';
	settings.context = $(this);
	settings.success = function(r, textStatus, jqXHR) {
		if (r.content) {
			$(this).replaceWith(r.content);
		} else {
			$.debug("failed to refresh");
		}
	};
	settings.data.instructions = instructions;
	var request = jQuery.ajax(settings);
});


$(document).on('click.cascade-api', '.refreshable a[data-state-change]', function(e) {
	e.preventDefault();
	var $refreshableParent = $(this).parents('.refreshable').first();
	$refreshableParent.trigger('refresh', [{state: $(this).data('state-change')}]);
	return false;
});