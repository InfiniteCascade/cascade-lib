var highlightObjectId;
function checkHighlight() {
	$("body[data-highlight]").each(function() {
		highlightObjectId = $(this).data('highlight');
		return false;
	});
}

function highlightObject(object) {
	for (var i = 1; i < 6; i++) {
		setTimeout(function() {
			$(object).effect('highlight', {'easing': 'easeInOutBack'}, 1000);
		}, i*1200);
    }
}

$preparer.add(function(context) {
	if (!highlightObjectId) {
		checkHighlight();
	}
	if (highlightObjectId) {
		$("[data-object-id="+highlightObjectId+"]").each(function() {
			highlightObject(this);
		});
	}
	$(".watch-link", context).on('startedWatching', function() {
		var $sibling = $(this).siblings('.watch-link.hidden');
		if ($sibling.length > 0) {
			if ($sibling.data('watch-task') === 'stop') {
				$sibling.removeClass('hidden');
				$(this).addClass('hidden');
			}
		}
	});
	$(".watch-link", context).on('stoppedWatching', function() {
		var $sibling = $(this).siblings('.watch-link.hidden');
		if ($sibling.length > 0) {
			if ($sibling.data('watch-task') === 'start') {
				$sibling.removeClass('hidden');
				$(this).addClass('hidden');
			}
		}
	});
});