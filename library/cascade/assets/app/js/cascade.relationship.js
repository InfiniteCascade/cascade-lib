var relationshipDefaults = {
	'inputLabel': 'Choose',
	'browseLabel': 'Browse',
	'multiple': false,
	'select': false, // object to select on startup
	'relationshipSeparator': '.',
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
			$.debug(relationshipParts);
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
		var searchSelectCallback = function(object, datum) {
			$this.select(datum);
		};
		var browseSelectCallback = function(datum) {
			$this.select(datum);
		};
		var searchOptions = jQuery.extend(true, {}, $this.options.search, {data: selectQueryData});
		$.debug(searchOptions);
		searchOptions.callback = searchSelectCallback;
		$this.elements.input.objectSearch(searchOptions);
		$this.elements.inputAddon = $("<span />", {'class': 'input-group-btn'}).appendTo($this.elements.inputGroup);
		$this.elements.browseArea = $("<div />", {'class': 'object-browse-container'}).appendTo($this.elements.selector);
		$this.elements.browseButton = $("<button />", {'class': 'btn btn-default', 'type': 'button'}).html($this.options.browseLabel).appendTo($this.elements.inputAddon);
		$this.elements.browseButton.click(function() {
			var browseOptions = jQuery.extend(true, {}, $this.options.browse, {data: browseQueryData});
			browseOptions.callback = browseSelectCallback;
			var objectBrowser = $this.elements.browseArea.objectBrowse(browseOptions);
			if (objectBrowser.visible) {
				$this.elements.browseButton.text('Browse');
				$this.elements.input.attr({'disabled': false});
				$this.elements.input.val($this.elements.input.data('previousValue'));
				objectBrowser.hide();
			} else {
				$this.elements.browseButton.text('Search');
				$this.elements.input.attr({'disabled': true});
				$this.elements.input.data('previousValue', $this.elements.input.val());
				$this.elements.input.val('Browsing...');
				objectBrowser.show();
			}
		});


		$this.elements.selectedPreview = $("<div />").addClass('relationship-preview panel panel-default').appendTo($this.elements.canvas);


		$this.select = function(datum) {
			$this.val(datum.id);
			$this.elements.selectedPreview.html('');
			$this.elements.selectedHeader = $("<div />", {'class': 'panel-heading'}).appendTo($this.elements.selectedPreview);
			$this.elements.selectedBody = $("<div />", {'class': 'panel-body'}).appendTo($this.elements.selectedPreview);
			$this.elements.selectedDescriptor = $("<h3 />", {'class': 'panel-title'}).html(datum.descriptor).appendTo($this.elements.selectedHeader);

			$this.elements.selectedSubdescriptor = $("<div />").html(datum.subdescriptor).appendTo($this.elements.selectedBody);
			$this.elements.selectedMenu = $("<div />", {'class': 'btn-group'}).appendTo($this.elements.selectedBody);
			$("<a />", {'href': '#', 'class': 'btn btn-primary btn-xs'}).html('Reselect').click(function() { $this.reset(); return false; }).appendTo($this.elements.selectedMenu);
			$this.elements.selector.hide();
			$this.elements.selectedPreview.show();
		};

		$this.reset = function(datum) {
			$this.val('');
			$this.elements.selector.show();
			$this.elements.selectedPreview.hide();
		};

		if ($this.options.select) {
			$this.select($this.options.select);
		}
	});
});