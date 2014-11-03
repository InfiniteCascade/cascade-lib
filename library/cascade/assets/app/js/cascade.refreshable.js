var refreshableDeferred;
var refreshableRequests = {};
var refreshableCount = 0;


function startRefreshableDeferred(options) {
	if (refreshableDeferred  && refreshableDeferred.state() === 'pending' && refreshableDeferred.state() !== 'handling') {
		refreshableDeferred.handle();
	}
	delete refreshableDeferred;
	if (options === undefined || typeof options !== 'object') {
		options = {};
	}
	if (options.timer === undefined) {
		options.timer = true;
	}
	refreshableDeferred = jQuery.Deferred();
	refreshableDeferred.substate = 'building';
	refreshableDeferred.timer = options.timer;
	refreshableDeferred.requests = {};
	refreshableDeferred.handle = function() {
		if (refreshableDeferred.substate === 'handling') {
			return false;
		}
		refreshableDeferred.substate = 'handling';
		var settings = {'data': {}, 'stream': false};
		settings = jQuery.extend(true, settings, $('body').data('refreshable'));
		var stream = settings.stream;
		delete settings['stream'];

		settings.data.requests = {};
		if (_.isEmpty(refreshableDeferred.requests)) {
			refreshableDeferred.resolve();
			return true;
		}

		jQuery.each(refreshableDeferred.requests, function(index, requestObject) {
			if (requestObject.result) { return true; }
			settings.data.requests[index] = requestObject.request;
		});

		if (_.isEmpty(settings.data.requests)) {
			refreshableDeferred.resolve();
			return;
		}
		settings.dataType = 'json';
		settings.type = 'POST';
		settings.context = $(this);
		var url = settings.url;

		if (stream) {
			vibe.open(url, {
				transports: ['streamxhr'],
				params: {open: settings.data},
				reconnect: function(lastDelay, attempts) {
			    	return false;
			    }
			}).on('handleRequests',
					function(data) {
				    	if (_.isEmpty(data)) { return; }
				    	jQuery.each(data, function(index, requestObject) {
							if (requestObject.content !== undefined && refreshableRequests[index] !== undefined) {
								refreshableRequests[index].result = requestObject.content;
								$replaceContent = $(refreshableRequests[index].result);
								$(refreshableRequests[index].object).replaceWith($replaceContent);
								$preparer.fire($replaceContent);
								delete refreshableRequests[index];
							}
						});
				    }
				);
			refreshableDeferred.resolve();
		} else {
			settings.success = function(r, textStatus, jqXHR) {
				if (r.requests !== undefined) {
					jQuery.each(r.requests, function(index, requestObject) {
						if (requestObject.content !== undefined && refreshableRequests[index] !== undefined) {
							refreshableRequests[index].result = requestObject.content;
							$replaceContent = $(refreshableRequests[index].result);
							$(refreshableRequests[index].object).replaceWith($replaceContent);
							$preparer.fire($replaceContent);
							delete refreshableRequests[index];
						}
					});
					jqXHR.refreshableDeferred.resolve();
				} else {
					jqXHR.refreshableDeferred.reject();
				}
			};
			settings.error = function (jqXHR) {
				jqXHR.refreshableDeferred.reject();
			};
			var request = jQuery.ajax(settings);
			request.refreshableDeferred = refreshableDeferred;
		}
	};
}
function handleRefresh(object, request) {
	if (!refreshableDeferred || (refreshableDeferred.state !== undefined) && refreshableDeferred.state() !== 'pending') {
		startRefreshableDeferred({'timer': true});
	}
	if (refreshableDeferred.timer) {
		clearTimeout(refreshableDeferred.timer);
		refreshableDeferred.timer = true;
	}
	var requestId = 'request-' + refreshableCount;
	refreshableCount++;
	refreshableDeferred.requests[requestId] = refreshableRequests[requestId] = {'object': object, 'request': request, 'result': false};
	if (refreshableDeferred.timer) {
		refreshableDeferred.timer = setTimeout(refreshableDeferred.handle, 200);
	}
}

jQuery.fn.extend({
  refreshable: function() {
  	if (this.length === 0) { return; }
  	startRefreshableDeferred({'timer': false});
    this.trigger('refresh');
    return refreshableDeferred.handle();
  }
});

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
});


$(document).on('click.cascade-api', '.refreshable a[data-state-change]', function(e) {
	e.preventDefault();
	var $refreshableParent = $(this).parents('.refreshable').first();
	$refreshableParent.trigger('refresh', [{state: $(this).data('state-change')}]);
	return false;
});
