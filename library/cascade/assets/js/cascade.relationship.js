var relationshipDefaults = {
	'inputLabel': 'Choose',
	'browseLabel': 'Browse',
	'multiple': false,
	'relationshipSeparator': '-',
	'context': {
	    'relationship': false,
	    'objectId': false,
	    'role': false
    },
	'browse': {
		'data': {'preview': true}
	},
	'search': {
		'data': {'preview': true}
	}
};

$preparer.add(function(context) {
	$("input[type=hidden].relationship", context).each(function() {
		var $this = $(this);
		$this.options = jQuery.extend(true, {}, relationshipDefaults, $this.data('relationship'));
		$.debug($this.options);
		var baseQueryData = {};
		if ($this.options.context.relationship && $this.options.context.role) {
			var relationshipParts = $this.options.context.relationship.split($this.options.relationshipSeparator);
			if ($this.options.context.role === 'child') {
				baseQueryData['modules'] = [relationshipParts[1]];
			} else {
				baseQueryData['modules'] = [relationshipParts[0]];
			}
		}

		var selectQueryData = jQuery.extend(true, {}, baseQueryData);
		var browseQueryData = jQuery.extend(true, {}, baseQueryData);

		if ($this.options.context.objectId) {
			if ($this.options.context.relationship && $this.options.context.role) {
				if ($this.options.context.role === 'child') {
					selectQueryData['ignoreParents'] = [$this.options.context.objectId];
				} else {
					selectQueryData['ignoreChildren'] = [$this.options.context.objectId];
				}
			}
			selectQueryData['ignore'] = browseQueryData['ignore'] = [$this.options.context.objectId];
		}


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
		var searchOptions = jQuery.extend(true, {}, $this.options.search, {data: selectQueryData});
		searchOptions.callback = selectCallback;
		$this.elements.input.objectSearch(searchOptions);
		$this.elements.inputAddon = $("<span />", {'class': 'input-group-btn'}).appendTo($this.elements.inputGroup);
		$this.elements.browseButton = $("<button />", {'class': 'btn btn-default', 'type': 'button'}).html($this.options.browseLabel).appendTo($this.elements.inputAddon);
		$this.elements.browseButton.click(function() {
			var browseOptions = jQuery.extend(true, {}, $this.options.browse, {data: browseQueryData});
			browseOptions.callback = selectCallback;
			$(document).browseObjects(browseOptions);
		});
	});
});