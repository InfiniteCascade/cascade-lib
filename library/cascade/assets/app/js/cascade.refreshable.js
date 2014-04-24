var refreshableDeferred;

function handleRefresh(object, request) {
	if (!refreshableDeferred || (refreshableDeferred.state !== undefined) && refreshableDeferred.state() !== 'pending') {
		refreshableDeferred = jQuery.Deferred();
		refreshableDeferred.timer = false;
		refreshableDeferred.requests = {};
		refreshableDeferred.requestCount = 0;
		refreshableDeferred.handle = function() {
			var settings = {'data': {}};
			settings = jQuery.extend(true, settings, $('body').data('refreshable'));
			settings.data.requests = {};
			jQuery.each(refreshableDeferred.requests, function(index, requestObject) {
				if (requestObject.result) { return true; }
				settings.data.requests[index] = requestObject.request;
			});
			settings.dataType = 'json';
			settings.type = 'POST';
			settings.context = $(this);
			settings.success = function(r, textStatus, jqXHR) {
				if (r.requests !== undefined) {
					jQuery.each(r.requests, function(index, requestObject) {
						if (requestObject.content !== undefined) {
							refreshableDeferred.requests[index].result = requestObject.content;
						}
					});
					refreshableDeferred.resolve();
				} else {
					refreshableDeferred.reject();
				}
			};
			settings.error = function () {
				refreshableDeferred.reject();
			};
			var request = jQuery.ajax(settings);
		};
	}
	if (refreshableDeferred.timer) {
		clearTimeout(refreshableDeferred.timer);
	}
	var requestId = 'request-' + refreshableDeferred.requestCount;
	refreshableDeferred.requestCount++;
	refreshableDeferred.requests[requestId] = {'object': object, 'request': request, 'result': false};
	refreshableDeferred.done(function() {
		if (refreshableDeferred.requests[requestId].result) {
			$(refreshableDeferred.requests[requestId].object).replaceWith(refreshableDeferred.requests[requestId].result);
			delete refreshableDeferred.requests[requestId];
		}
	});
	refreshableDeferred.timer = setTimeout(refreshableDeferred.handle, 200);
}

$(document).on('refresh.cascade-api', '.refreshable', function(e, data) {
	
	var instructions = {};
    if (typeof data !== 'object') {
    	data = {};
    }
	if (typeof $(this).data('instructions') === 'object' ) {
		instructions = jQuery.extend(true, instructions, $(this).data('instructions'));
	}
	data.instructions = instructions;
	handleRefresh(this, data);

/*	settings.dataType = 'json';
	settings.type = 'GET';
	settings.context = $(this);
	settings.success = function(r, textStatus, jqXHR) {
		if (r.content) {
			$(this).replaceWith(r.content);
		} else {
			$.debug("failed to refresh");
		}
	};
	settings.data.instructions = instructions;
	var request = jQuery.ajax(settings);*/
});


$(document).on('click.cascade-api', '.refreshable a[data-state-change]', function(e) {
	e.preventDefault();
	var $refreshableParent = $(this).parents('.refreshable').first();
	$refreshableParent.trigger('refresh', [{state: $(this).data('state-change')}]);
	return false;
});