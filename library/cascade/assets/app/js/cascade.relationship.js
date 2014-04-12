var relationshipDefaults = {
	'multiple': false,
	'select': false, // object to select on startup
	'justFields': false,
	'context': {
    },
    'selector': {
    },
    'model': {
    	'prefix': null,
    	'attributes': {}
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
				var temporalCanvas = $('<div />', {'class': 'relationship-field relationship-field-temporal form-inline'}).appendTo($target);
				var startDateGroup = $('<div />', {'class': 'form-group input-group date'}).appendTo(temporalCanvas);
				var startDateInput = $('<input />', {'class': 'form-control ignore-focus', 'placeholder': 'Start', 'type': 'text', 'name': $this.options.model.prefix + '[start]'}).appendTo(startDateGroup);
				$('<span />', {'class': 'input-group-addon'}).html('<i class="fa fa-calendar"></i>').appendTo(startDateGroup);
				if ($this.options.model.attributes.start === undefined) {
					$this.options.model.attributes.start = '';
				}
				startDateInput.val($this.options.model.attributes.start);
				var toDate = $("<span />", {'class': 'relationship-date-separator'}).html("to").appendTo(temporalCanvas);
				var endDateGroup = $('<div />', {'class': 'form-group input-group date'}).appendTo(temporalCanvas);
				var endDateInput = $('<input />', {'class': 'form-control ignore-focus', 'placeholder': 'End', 'type': 'text', 'name': $this.options.model.prefix + '[end]'}).appendTo(endDateGroup);
				$('<span />', {'class': 'input-group-addon'}).html('<i class="fa fa-calendar"></i>').appendTo(endDateGroup);
				if ($this.options.model.attributes.end === undefined) {
					$this.options.model.attributes.end = '';
				}
				endDateInput.val($this.options.model.attributes.end);
			}
			if ($this.options.context.relationship.taxonomy) {
				var taxonomy = $this.options.context.relationship.taxonomy;
				var taxonomyCanvas = $('<div />', {'class': 'relationship-field relationship-field-taxonomy'}).appendTo($target);
				var taxonomySelectGroup = $('<div />', {'class': 'form-group'}).appendTo(taxonomyCanvas);
				var taxonomySelectLabel = $('<label />', {'class': ''}).html(taxonomy.name).appendTo(taxonomySelectGroup);
				var taxonomySelectInput = $('<select />', {'class': 'form-control ignore-focus', 'name': $this.options.model.prefix + '[taxonomy_id]'}).appendTo(taxonomySelectGroup);
				if ($this.options.model.attributes.taxonomy_id === undefined) {
					$this.options.model.attributes.taxonomy_id = [];
				}
				taxonomySelectInput.renderSelect(taxonomy.taxonomies, this.required, $this.options.model.attributes.taxonomy_id);
				if (taxonomy.multiple) {
					taxonomySelectInput.attr("multiple", true);
				}
			}
			if ($this.options.context.relationship.activeAble) {
				var activeCanvas = $('<div />', {'class': 'relationship-field relationship-field-active'}).appendTo($target);
				var activeGroup = $('<div />', {'class': 'checkbox'}).appendTo(activeCanvas);
				var activeLabel = $('<label />', {'class': ''}).html("Active Link").appendTo(activeGroup);
				if ($this.options.model.attributes.active === undefined || $this.options.model.attributes.active === null) {
					$this.options.model.attributes.active = 1;
				}
				var activeInput = $("<input />", {'type': 'checkbox', 'class': 'ignore-focus', 'name': $this.options.model.prefix + '[active]'}).prependTo(activeLabel);
				if ($this.options.model.attributes.active) {
					activeInput.attr('checked', true);
				}
			}
			prepareCascadeFormFields($target);
		};

      	$this.relationshipElements = {};
      	$this.relationshipElements.canvas = $("<div />").addClass('relationship-canvas').insertAfter($this);
      	if ($this.options.justFields) {
      		if ($this.hasFields()) {
				$this.relationshipElements.fields = $("<div />").addClass('relationship-object-fields-alone').appendTo($this.relationshipElements.canvas);
				$this.buildFields($this.relationshipElements.fields);
			}
      	} else {
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
		}

	});
});