var refreshableDeferred;
var refreshableRequests = {};
var refreshableCount = 0;


function startRefreshableDeferred(timer) {
	if (refreshableDeferred  && refreshableDeferred.state() === 'pending' && refreshableDeferred.state() !== 'handling') {
		refreshableDeferred.handle();
	}
	if (timer === undefined) {
		timer = true;
	}
	refreshableDeferred = jQuery.Deferred();
	refreshableDeferred.substate = 'building';
	refreshableDeferred.timer = timer;
	refreshableDeferred.requests = {};
	refreshableDeferred.handle = function() {
		if (this.substate === 'handling') { return false; }
		this.substate = 'handling';
		var settings = {'data': {}};
		settings = jQuery.extend(true, settings, $('body').data('refreshable'));
		settings.data.requests = {};
		jQuery.each(this.requests, function(index, requestObject) {
			if (requestObject.result) { return true; }
			settings.data.requests[index] = requestObject.request;
		});
		if (_.isEmpty(settings.data.requests)) {
			this.resolve();
			return;
		}
		settings.dataType = 'json';
		settings.type = 'POST';
		settings.context = $(this);
		settings.success = function(r, textStatus, jqXHR) {
			if (r.requests !== undefined) {
				jQuery.each(r.requests, function(index, requestObject) {
					if (requestObject.content !== undefined) {
						refreshableRequests[index].result = requestObject.content;
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
	};
}
function handleRefresh(object, request) {
	if (!refreshableDeferred || (refreshableDeferred.state !== undefined) && refreshableDeferred.state() !== 'pending') {
		startRefreshableDeferred(true);
	}
	if (refreshableDeferred.timer) {
		clearTimeout(refreshableDeferred.timer);
		refreshableDeferred.timer = true;
	}
	var requestId = 'request-' + refreshableCount;
	refreshableCount++;
	refreshableDeferred.requests[requestId] = refreshableRequests[requestId] = {'object': object, 'request': request, 'result': false};
	refreshableDeferred.done(function() {
		if (refreshableRequests[requestId] === undefined){
			$.debug([requestId, refreshableRequests]);
			return;
		}
		if (refreshableRequests[requestId].result) {
			$replaceContent = $(refreshableRequests[requestId].result);
			$(refreshableRequests[requestId].object).replaceWith($replaceContent);
			$preparer.fire($replaceContent);
			delete refreshableRequests[requestId];
		}
	});
	if (refreshableDeferred.timer) {
		refreshableDeferred.timer = setTimeout(refreshableDeferred.handle, 3000);
	}
}

jQuery.fn.extend({
  refreshable: function() {
  	startRefreshableDeferred(false);
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