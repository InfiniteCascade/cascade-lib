$preparer.add(function(context) {
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