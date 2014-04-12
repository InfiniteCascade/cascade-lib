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

		$this.hasFields = function() {
			if ($this.options.context.relationship.temporal) {
				return true;
			}
			if ($this.options.context.relationship.taxonomy) {
				return true;
			}
			if ($this.options.context.relationship.activeAble) {
				return true;
			}
			return false;
		};


		$this.buildFields = function($target) {
			if ($this.options.context.relationship.temporal) {
				var temporalCanvas = $('<div />', {'class': 'relationship-field relationship-field-temporal'}).appendTo($target);
			}
			if ($this.options.context.relationship.taxonomy) {
				var taxonomyCanvas = $('<div />', {'class': 'relationship-field relationship-field-taxonomy'}).appendTo($target);

			}
			if ($this.options.context.relationship.activeAble) {
				var activeCanvas = $('<div />', {'class': 'relationship-field relationship-field-active'}).appendTo($target);
				
			}
		};

      	$this.relationshipElements = {};
      	$this.relationshipElements.canvas = $("<div />").addClass('relationship-canvas').insertAfter($this);
		$this.relationshipElements.relationshipCanvas = $("<div />").addClass('relationship-object-canvas').appendTo($this.relationshipElements.canvas);
		$this.relationshipElements.parentObject = $("<div />").addClass('relationship-object').appendTo($this.relationshipElements.relationshipCanvas);
		$this.relationshipElements.separator = $("<div />").addClass('relationship-object-separator').appendTo($this.relationshipElements.relationshipCanvas);
		if ($this.hasFields()) {
			$this.relationshipElements.fields = $("<div />").addClass('relationship-object-fields').appendTo($this.relationshipElements.relationshipCanvas);
			$this.buildFields($this.relationshipElements.fields);
			$this.relationshipElements.separator = $("<div />").addClass('relationship-object-separator').appendTo($this.relationshipElements.relationshipCanvas);
		}
		$this.relationshipElements.childObject = $("<div />").addClass('relationship-object').appendTo($this.relationshipElements.relationshipCanvas);
		
		if ($this.context.role === 'child') {
			$this.relationshipElements.originalObjectTarget = $this.relationshipElements.parentObject;
			$this.relationshipElements.relatedObjectTarget = $this.relationshipElements.childObject;
		} else {
			$this.relationshipElements.originalObjectTarget = $this.relationshipElements.parentObject;
			$this.relationshipElements.relatedObjectTarget = $this.relationshipElements.childObject;
		}
		$this.relationshipElements.originalObjectTarget.append($('<div />', {'class': 'relationship-object-descriptor'}).html($this.options.context.object.descriptor));
		if ($this.options.context.object.subdescriptor !== undefined) {
			$this.relationshipElements.originalObjectTarget.append($('<div />', {'class': 'relationship-object-subdescriptor'}).html($this.options.context.object.subdescriptor));
		}

		$this.relationshipElements.relatedSelectedObjectTarget = $('<div />').appendTo($this.relationshipElements.relatedObjectTarget);

		var selectorOptions = $this.options.selector;
		$this.select = function($selector, datum) {
			$selector.hideSelector();
			$this.val(datum.id);
			$.debug($this.relationshipElements);
			$this.relationshipElements.relatedSelectedObjectTarget.html('');
			$this.relationshipElements.relatedSelectedObjectTarget.append($('<div />', {'class': 'relationship-object-descriptor'}).html(datum.descriptor));
			if (datum.subdescriptor !== undefined) {
				$this.relationshipElements.relatedSelectedObjectTarget.append($('<div />', {'class': 'relationship-object-subdescriptor'}).html(datum.subdescriptor));
			}
			$this.relationshipElements.relatedSelectedObjectTarget.append($("<a />", {'href': '#', 'class': 'btn btn-primary'}).html('Reselect').click(function() { $this.resetRelationship(); return false; }));
		};

		$this.resetRelationship = function(datum) {
			$this.val('');
			$this.relationshipElements.selectedPreview.hide();
			$this.showSelector();
		};


		selectorOptions.callback = $this.select;
		selectorOptions.context = $this.options.context;
		selectorOptions.canvasTarget = $this.relationshipElements.relatedObjectTarget;
		$this.objectSelector(selectorOptions);
		if ($this.options.select) {
			$this.select($this.options.select);
		}

	});
});