var relationshipDefaults = {
	'title': 'Relationship',
	'multiple': false,
	'select': false, // object to select on startup
	'justFields': false,
	'context': {
    },
    'selector': {
    },
    'lockFields': [],
    'model': {
    	'prefix': null,
    	'attributes': {}
    }
	
};

$preparer.add(function(context) {
	$("input[type=hidden].relationship", context).each(function() {
		var $this = $(this);

		$this.options = jQuery.extend(true, {}, relationshipDefaults, $this.data('relationship'));
		if (!_.isArray($this.options.lockFields)) {
			$this.options.lockFields = _.values($this.options.lockFields);
		}
		$.debug($this.options);

		$this.hasFields = function() {
			if ($this.options.context.relationship.temporal) {
				if (!(jQuery.inArray('start', $this.options.lockFields) > -1 && jQuery.inArray('end', $this.options.lockFields) > -1)) {
					return true;
				}
			}
			if ($this.options.context.relationship.taxonomy) {
				if (jQuery.inArray('taxonomy_id', $this.options.lockFields) === -1) {
					return true;
				}
			}
			if ($this.options.context.relationship.activeAble) {
				if (jQuery.inArray('active', $this.options.lockFields) === -1) {
					return true;
				}
			}
			return false;
		};


		$this.buildFields = function($target) {
			if ($this.options.context.relationship.temporal && !(jQuery.inArray('start', $this.options.lockFields) > -1 && jQuery.inArray('end', $this.options.lockFields) > -1)) {
				var temporalCanvas = $('<div />', {'class': 'relationship-field relationship-field-temporal form-inline'}).appendTo($target);
				var startDateGroup = $('<div />', {'class': 'form-group input-group date'}).appendTo(temporalCanvas);
				var startDateInput = $('<input />', {'class': 'form-control ignore-focus', 'placeholder': 'Start', 'type': 'text', 'name': $this.options.model.prefix + '[start]'}).appendTo(startDateGroup);
				$('<span />', {'class': 'input-group-addon'}).html('<i class="fa fa-calendar"></i>').appendTo(startDateGroup);
				if ($this.options.model.attributes.start === undefined) {
					$this.options.model.attributes.start = '';
				}
				if (jQuery.inArray('start', $this.options.lockFields)) {
					startDateInput.prop('disabled', true);
				}
				startDateInput.val($this.options.model.attributes.start);
				var toDate = $("<span />", {'class': 'relationship-date-separator'}).html("to").appendTo(temporalCanvas);
				var endDateGroup = $('<div />', {'class': 'form-group input-group date'}).appendTo(temporalCanvas);
				var endDateInput = $('<input />', {'class': 'form-control ignore-focus', 'placeholder': 'End', 'type': 'text', 'name': $this.options.model.prefix + '[end]'}).appendTo(endDateGroup);
				$('<span />', {'class': 'input-group-addon'}).html('<i class="fa fa-calendar"></i>').appendTo(endDateGroup);
				if ($this.options.model.attributes.end === undefined) {
					$this.options.model.attributes.end = '';
				}
				if (jQuery.inArray('end', $this.options.lockFields)) {
					endDateInput.prop('disabled', true);
				}
				endDateInput.val($this.options.model.attributes.end);
			}
			if ($this.options.context.relationship.taxonomy && jQuery.inArray('taxonomy_id', $this.options.lockFields) === -1) {
				$.debug($this.options.lockFields);
				var taxonomy = $this.options.context.relationship.taxonomy;
				var taxonomyCanvas = $('<div />', {'class': 'relationship-field relationship-field-taxonomy'}).appendTo($target);
				var taxonomySelectGroup = $('<div />', {'class': 'form-group'}).appendTo(taxonomyCanvas);
				var taxonomySelectLabel = $('<label />', {'class': ''}).html(taxonomy.name).appendTo(taxonomySelectGroup);
				var taxonomySelectInput = $('<select />', {'class': 'form-control ignore-focus', 'name': $this.options.model.prefix + '[taxonomy_id][]'}).appendTo(taxonomySelectGroup);
				if ($this.options.model.attributes.taxonomy_id === undefined) {
					$this.options.model.attributes.taxonomy_id = [];
				}
				if (taxonomy.multiple) {
					taxonomySelectInput.attr("multiple", true);
				}
				taxonomySelectInput.renderSelect(taxonomy.taxonomies, this.required, $this.options.model.attributes.taxonomy_id);
			}
			if ($this.options.context.relationship.activeAble && jQuery.inArray('active', $this.options.lockFields) === -1) {
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
      			var title = 'Relationship to <em>'+ $this.options.context.object.descriptor +'</em>';

				$this.relationshipElements.fieldsContainer = $("<div />").addClass('panel panel-default').appendTo($this.relationshipElements.canvas);
				$this.relationshipElements.fieldsContainerHeading = $("<div />").addClass('panel-heading').appendTo($this.relationshipElements.fieldsContainer);
				$this.relationshipElements.fieldsContainerHeadingTitle = $("<h4 />").html(title).addClass('panel-title').appendTo($this.relationshipElements.fieldsContainerHeading);
				$this.relationshipElements.fieldsPanelBody = $("<div />").addClass('panel-body').appendTo($this.relationshipElements.fieldsContainer);
				$this.relationshipElements.fields = $("<div />").addClass('relationship-object-fields-alone').appendTo($this.relationshipElements.fieldsPanelBody);
				$this.buildFields($this.relationshipElements.fields);
			}
      	} else {
			$this.relationshipElements.relationshipCanvas = $("<div />").addClass('relationship-object-canvas').appendTo($this.relationshipElements.canvas);
			$this.relationshipElements.parentObject = $("<div />").addClass('relationship-object').appendTo($this.relationshipElements.relationshipCanvas);
			$this.relationshipElements.parentSeparator = $("<div />").addClass('relationship-object-separator').appendTo($this.relationshipElements.relationshipCanvas);
			if ($this.hasFields()) {
				$this.relationshipElements.fields = $("<div />").addClass('relationship-object-fields').appendTo($this.relationshipElements.relationshipCanvas);
				$this.buildFields($this.relationshipElements.fields);
				$this.relationshipElements.childSeparator = $("<div />").addClass('relationship-object-separator').appendTo($this.relationshipElements.relationshipCanvas);
			} else {
				$this.relationshipElements.childSeparator = $("<div />");
			}
			$this.relationshipElements.childObject = $("<div />").addClass('relationship-object').appendTo($this.relationshipElements.relationshipCanvas);
			
			if ($this.context.role === 'child') {
				$this.relationshipElements.originalObjectTarget = $this.relationshipElements.childObject;
				$this.relationshipElements.originalObjectSeparatorTarget = $this.relationshipElements.childSeparator;
				$this.relationshipElements.relatedObjectTarget = $this.relationshipElements.parentObject;
				$this.relationshipElements.relatedObjectSeparatorTarget = $this.relationshipElements.parentSeparator;
			} else {
				$this.relationshipElements.originalObjectTarget = $this.relationshipElements.parentObject;
				$this.relationshipElements.originalObjectSeparatorTarget = $this.relationshipElements.parentSeparator;
				$this.relationshipElements.relatedObjectTarget = $this.relationshipElements.childObject;
				$this.relationshipElements.relatedObjectSeparatorTarget = $this.relationshipElements.childSeparator;
			}

			var selectorOptions = $this.options.selector;

			if ($this.options.context.object !== undefined && $this.options.context.object) {
				$this.relationshipElements.originalObjectTarget.append($('<div />', {'class': 'relationship-object-descriptor'}).html($this.options.context.object.descriptor));
				if ($this.options.context.object.subdescriptor !== undefined) {
					$this.relationshipElements.originalObjectTarget.append($('<div />', {'class': 'relationship-object-subdescriptor'}).html($this.options.context.object.subdescriptor));
				}
				$this.relationshipElements.relatedSelectedObjectTarget = $('<div />').prependTo($this.relationshipElements.relatedObjectTarget);
				selectorOptions.canvasTarget = $this.relationshipElements.relatedObjectTarget;
			} else {
				var title = $this.options.title;
				$this.relationshipElements.originalObjectTarget.remove();
				$this.relationshipElements.originalObjectSeparatorTarget.remove();
				$this.relationshipElements.relatedObjectSeparatorTarget.remove();
				$this.relationshipElements.relatedObjectTarget.removeClass('relationship-object').addClass('panel panel-default');
				$this.relationshipElements.relatedContainerHeading = $("<div />").addClass('panel-heading').appendTo($this.relationshipElements.relatedObjectTarget);
				$this.relationshipElements.relatedContainerHeadingTitle = $("<h4 />").html(title).addClass('panel-title').appendTo($this.relationshipElements.relatedContainerHeading);
				$this.relationshipElements.relatedContainerBody = $("<div />").addClass('panel-body').appendTo($this.relationshipElements.relatedObjectTarget);
				
				$this.relationshipElements.relatedSelectedObjectTarget = $('<div />', {'class': 'relationship-object-selected'}).appendTo($this.relationshipElements.relatedContainerBody);

				if ($this.relationshipElements.fields !== undefined) {
					$this.relationshipElements.fields.removeClass('relationship-object-fields');
					$this.relationshipElements.fields.addClass('relationship-object-fields-alone');
					$this.relationshipElements.fields.appendTo($this.relationshipElements.relatedContainerBody);
				}
				selectorOptions.canvasTarget = $this.relationshipElements.relatedContainerBody;
			}


			$this.select = function($selector, datum) {
				$selector.hideSelector();
				$this.val(datum.id);
				$.debug($this.relationshipElements);
				$this.relationshipElements.relatedSelectedObjectTarget.show();
				$this.relationshipElements.relatedSelectedObjectTarget.html('');
				$this.relationshipElements.relatedSelectedObjectTarget.append($('<div />', {'class': 'relationship-object-descriptor'}).html(datum.descriptor));
				if (datum.subdescriptor !== undefined) {
					$this.relationshipElements.relatedSelectedObjectTarget.append($('<div />', {'class': 'relationship-object-subdescriptor'}).html(datum.subdescriptor));
				}
				$this.relationshipElements.relatedSelectedObjectTarget.append($("<a />", {'href': '#', 'class': 'btn btn-primary'}).html('Reselect').click(function() { $this.resetRelationship(); return false; }));
			};

			$this.resetRelationship = function(datum) {
				$this.val('');
				$this.relationshipElements.relatedSelectedObjectTarget.hide();
				$this.showSelector();
			};


			selectorOptions.callback = $this.select;
			selectorOptions.context = $this.options.context;
			$this.objectSelector(selectorOptions);
			if ($this.options.select) {
				$this.select($this.options.select);
			}
		}

	});
});