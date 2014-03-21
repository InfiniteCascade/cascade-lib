$preparer.add(function(context) {
	$("[data-access]").each(function() {
		var $this = $(this);
		var $form = $(this).parents('form').first();
		var options = $(this).data('access');
		$.debug(options);
	});
});