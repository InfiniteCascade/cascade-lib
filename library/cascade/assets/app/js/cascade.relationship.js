var relationshipDefaults = {
	'multiple': false,
	'select': false, // object to select on startup
	'context': {
    },
    'selector': {

    }
	
};

$preparer.add(function(context) {
	$("input[type=hidden].relationship", context).each(function() {
		var $this = $(this);
		$this.options = jQuery.extend(true, {}, relationshipDefaults, $this.data('relationship'));
		$.debug($this.options);
      	$this.relationshipElements = {};
      	$this.relationshipElements.canvas = $("<div />").addClass('relationship-canvas').insertAfter($this);
		$this.relationshipElements.selectedPreview = $("<div />").addClass('relationship-preview panel panel-default').appendTo($this.relationshipElements.canvas);
		var selectorOptions = $this.options.selector;
		$this.select = function(datum) {
			$this.val(datum.id);
			$.debug($this.relationshipElements);
			$this.relationshipElements.selectedPreview.html('');
			$this.relationshipElements.selectedHeader = $("<div />", {'class': 'panel-heading'}).appendTo($this.relationshipElements.selectedPreview);
			$this.relationshipElements.selectedBody = $("<div />", {'class': 'panel-body'}).appendTo($this.relationshipElements.selectedPreview);
			$this.relationshipElements.selectedDescriptor = $("<h3 />", {'class': 'panel-title'}).html(datum.descriptor).appendTo($this.relationshipElements.selectedHeader);

			$this.relationshipElements.selectedSubdescriptor = $("<div />").html(datum.subdescriptor).appendTo($this.relationshipElements.selectedBody);
			$this.relationshipElements.selectedMenu = $("<div />", {'class': 'btn-group'}).appendTo($this.relationshipElements.selectedBody);
			$("<a />", {'href': '#', 'class': 'btn btn-primary btn-xs'}).html('Reselect').click(function() { $this.resetRelationship(); return false; }).appendTo($this.relationshipElements.selectedMenu);
			$this.relationshipElements.selectedPreview.show();
		};

		$this.resetRelationship = function(datum) {
			$this.val('');
			$this.relationshipElements.selectedPreview.hide();
			$this.showSelector();
		};

		selectorOptions.callback = $this.select;
		$this.objectSelector(selectorOptions);
		if ($this.options.select) {
			$this.select($this.options.select);
		}

	});
});