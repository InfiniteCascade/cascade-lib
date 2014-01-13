var relationshipDefaults = {
	'inputLabel': 'Choose',
	'browseLabel': 'Browse',
	'multiple': false,
	'browse': {
		'url': '/browse',
		'data': {'preview': true}
	},
	'search': {}
};

$preparer.add(function(context) {
	$("input[type=hidden].relationship", context).each(function() {
		var $this = $(this);
		$this.options = jQuery.extend(true, {}, relationshipDefaults, $this.data('relationship'));
		$this.elements = {};
		$this.searchInputId = $this.attr("id") + '-search';
		$this.elements.canvas = $("<div />").addClass('relationship-canvas').insertAfter($this);
		$this.elements.selector = $("<div />").addClass('relationship-selector').appendTo($this.elements.canvas);
		$this.elements.label = $("<label />", {'for': $this.searchInputId}).html($this.options.inputLabel).appendTo($this.elements.selector);
		$this.elements.inputGroup = $("<div />", {'class': 'input-group'}).appendTo($this.elements.selector);
		$this.elements.input = $("<input />", {'type': 'text', 'class': 'form-control', 'id': $this.searchInputId}).appendTo($this.elements.inputGroup);
		var selectCallback = function(chosen) {
			$.debug(chosen);
		};
		var searchOptions = jQuery.extend(true, {}, $this.options.search);
		searchOptions.callback = selectCallback;
		$this.elements.input.objectSearch(searchOptions);
		$this.elements.inputAddon = $("<span />", {'class': 'input-group-btn'}).appendTo($this.elements.inputGroup);
		$this.elements.browseButton = $("<button />", {'class': 'btn btn-default', 'type': 'button'}).html($this.options.browseLabel).appendTo($this.elements.inputAddon);
		$this.elements.browseButton.click(function() {
			var browseOptions = jQuery.extend(true, {}, $this.options.browse);
			browseOptions.callback = selectCallback;
			$(document).browseObjects(browseOptions);
		});
	});
});