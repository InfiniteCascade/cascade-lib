var relationshipDefaults = {
	'title': 'Relationship',
	'multiple': false,
	'select': false, // object to select on startup
	'justFields': false,
	'context': {
    },
    'selector': {
    },
    'template': null,
    'lockFields': [],
    'model': {
    	'prefix': null,
    	'attributes': {}
    }
	
};

$preparer.add(function(context) {
	$("input[type=hidden].relationship", context).each(function() {
		var $this = $(this);

		$this.relationshipOptions = jQuery.extend(true, {}, relationshipDefaults, $this.data('relationship'));
		if (!_.isArray($this.relationshipOptions.lockFields)) {
			$this.relationshipOptions.lockFields = _.values($this.relationshipOptions.lockFields);
		}
		$.debug($this.relationshipOptions);

		$this.hasFields = function() {
			if ($this.relationshipOptions.context.relationship.temporal) {
				if (!(jQuery.inArray('start', $this.relationshipOptions.lockFields) > -1 && jQuery.inArray('end', $this.relationshipOptions.lockFields) > -1)) {
					return true;
				}
			}
			if ($this.relationshipOptions.context.relationship.taxonomy) {
				if (jQuery.inArray('taxonomy_id', $this.relationshipOptions.lockFields) === -1) {
					return true;
				}
			}
			if ($this.relationshipOptions.context.relationship.activeAble) {
				if (jQuery.inArray('active', $this.relationshipOptions.lockFields) === -1) {
					return true;
				}
			}
			return false;
		};


		$this.buildFields = function($target) {
			if ($this.relationshipOptions.context.relationship.temporal && !(jQuery.inArray('start', $this.relationshipOptions.lockFields) > -1 && jQuery.inArray('end', $this.relationshipOptions.lockFields) > -1)) {
				var temporalCanvas = $('<div />', {'class': 'relationship-field relationship-field-temporal form-inline'}).appendTo($target);
				var startDateGroup = $('<div />', {'class': 'form-group input-group date'}).appendTo(temporalCanvas);
				var startDateInput = $('<input />', {'class': 'form-control ignore-focus', 'placeholder': 'Start', 'type': 'text', 'name': $this.relationshipOptions.model.prefix + '[start]'}).appendTo(startDateGroup);
				$('<span />', {'class': 'input-group-addon'}).html('<i class="fa fa-calendar"></i>').appendTo(startDateGroup);
				if ($this.relationshipOptions.model.attributes.start === undefined) {
					$this.relationshipOptions.model.attributes.start = '';
				}
				if (jQuery.inArray('start', $this.relationshipOptions.lockFields) > -1) {
					startDateInput.prop('disabled', true);
				}
				startDateInput.val($this.relationshipOptions.model.attributes.start);
				var toDate = $("<span />", {'class': 'relationship-date-separator'}).html("to").appendTo(temporalCanvas);
				var endDateGroup = $('<div />', {'class': 'form-group input-group date'}).appendTo(temporalCanvas);
				var endDateInput = $('<input />', {'class': 'form-control ignore-focus', 'placeholder': 'End', 'type': 'text', 'name': $this.relationshipOptions.model.prefix + '[end]'}).appendTo(endDateGroup);
				$('<span />', {'class': 'input-group-addon'}).html('<i class="fa fa-calendar"></i>').appendTo(endDateGroup);
				if ($this.relationshipOptions.model.attributes.end === undefined) {
					$this.relationshipOptions.model.attributes.end = '';
				}
				if (jQuery.inArray('end', $this.relationshipOptions.lockFields) > -1) {
					endDateInput.prop('disabled', true);
				}
				endDateInput.val($this.relationshipOptions.model.attributes.end);
			}
			if ($this.relationshipOptions.context.relationship.taxonomy && jQuery.inArray('taxonomy_id', $this.relationshipOptions.lockFields) === -1) {
				$.debug($this.relationshipOptions.lockFields);
				var taxonomy = $this.relationshipOptions.context.relationship.taxonomy;
				var taxonomyCanvas = $('<div />', {'class': 'relationship-field relationship-field-taxonomy'}).appendTo($target);
				var taxonomySelectGroup = $('<div />', {'class': 'form-group'}).appendTo(taxonomyCanvas);
				var taxonomySelectLabel = $('<label />', {'class': ''}).html(taxonomy.name).appendTo(taxonomySelectGroup);
				var taxonomySelectInput = $('<select />', {'class': 'form-control ignore-focus', 'name': $this.relationshipOptions.model.prefix + '[taxonomy_id][]'}).appendTo(taxonomySelectGroup);
				if ($this.relationshipOptions.model.attributes.taxonomy_id === undefined) {
					$this.relationshipOptions.model.attributes.taxonomy_id = [];
				}
				if (taxonomy.multiple) {
					taxonomySelectInput.attr("multiple", true);
				}
				taxonomySelectInput.renderSelect(taxonomy.taxonomies, this.required, $this.relationshipOptions.model.attributes.taxonomy_id);
			}
			if ($this.relationshipOptions.context.relationship.activeAble && jQuery.inArray('active', $this.relationshipOptions.lockFields) === -1) {
				var activeCanvas = $('<div />', {'class': 'relationship-field relationship-field-active'}).appendTo($target);
				var activeGroup = $('<div />', {'class': 'checkbox'}).appendTo(activeCanvas);
				var activeLabel = $('<label />', {'class': ''}).html("Active Link").appendTo(activeGroup);
				if ($this.relationshipOptions.model.attributes.active === undefined || $this.relationshipOptions.model.attributes.active === null) {
					$this.relationshipOptions.model.attributes.active = 1;
				}
				var activeInput = $("<input />", {'type': 'checkbox', 'class': 'ignore-focus', 'name': $this.relationshipOptions.model.prefix + '[active]'}).prependTo(activeLabel);
				if ($this.relationshipOptions.model.attributes.active) {
					activeInput.attr('checked', true);
				}
			}
			prepareCascadeFormFields($target);
		};

		$this.templates = {
			'hierarchy': function($this, selectorOptions) {
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

				$this.relationshipElements.originalObjectTarget.append($('<div />', {'class': 'relationship-object-descriptor'}).html($this.relationshipOptions.context.object.descriptor));
				if ($this.relationshipOptions.context.object.subdescriptor !== undefined) {
					$this.relationshipElements.originalObjectTarget.append($('<div />', {'class': 'relationship-object-subdescriptor'}).html($this.relationshipOptions.context.object.subdescriptor));
				}
				$this.relationshipElements.relatedSelectedObjectTarget = $('<div />').prependTo($this.relationshipElements.relatedObjectTarget);
				selectorOptions.canvasTarget = $this.relationshipElements.relatedObjectTarget;
				return selectorOptions;
			},
			'fields': function($this, selectorOptions) {	
      			var title = 'Relationship to <em>'+ $this.relationshipOptions.context.object.descriptor +'</em>';
				$this.relationshipElements.fieldsContainer = $("<div />").addClass('panel panel-default').appendTo($this.relationshipElements.canvas);
				$this.relationshipElements.fieldsContainerHeading = $("<div />").addClass('panel-heading').appendTo($this.relationshipElements.fieldsContainer);
				$this.relationshipElements.fieldsContainerHeadingTitle = $("<h4 />").html(title).addClass('panel-title').appendTo($this.relationshipElements.fieldsContainerHeading);
				$this.relationshipElements.fieldsPanelBody = $("<div />").addClass('panel-body').appendTo($this.relationshipElements.fieldsContainer);
				$this.relationshipElements.fields = $("<div />").addClass('relationship-object-fields-alone').appendTo($this.relationshipElements.fieldsPanelBody);
				$this.buildFields($this.relationshipElements.fields);
				return false;
			}, 
			'simple': function($this, selectorOptions) {
				$this.relationshipElements.relatedObjectTarget = $("<div />").addClass('panel panel-default').appendTo($this.relationshipElements.canvas);
				$this.relationshipElements.relatedContainerHeading = $("<div />").addClass('panel-heading').appendTo($this.relationshipElements.relatedObjectTarget);
				$this.relationshipElements.relatedContainerHeadingTitle = $("<h4 />").html($this.relationshipOptions.title).addClass('panel-title').appendTo($this.relationshipElements.relatedContainerHeading);
				$this.relationshipElements.relatedContainerBody = $("<div />").addClass('panel-body').appendTo($this.relationshipElements.relatedObjectTarget);
				$this.relationshipElements.relatedSelectedObjectTarget = $('<div />', {'class': 'relationship-object-selected'}).prependTo($this.relationshipElements.relatedContainerBody);
				if ($this.hasFields()) {
					$this.relationshipElements.fields = $("<div />").addClass('relationship-object-fields-alone').appendTo($this.relationshipElements.relationshipCanvas);
					$this.buildFields($this.relationshipElements.fields);
				}
				selectorOptions.canvasTarget = $this.relationshipElements.relatedContainerBody;
				return selectorOptions;
			}
		};

      	$this.relationshipElements = {};
      	$this.relationshipElements.canvas = $("<div />").addClass('relationship-canvas').insertAfter($this);
      	if ($this.relationshipOptions.template === null || $this.templates[$this.relationshipOptions.template] === undefined) {
      		if ($this.relationshipOptions.context.object === undefined) {
      			$this.relationshipOptions.template = 'simple';
      		} else {
      			$this.relationshipOptions.template = 'hierarchy';
      		}
      	}
      	var selectorOptions = $this.templates[$this.relationshipOptions.template]($this, $this.relationshipOptions.selector);
      	
		if (selectorOptions) {
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
				if (jQuery.inArray('object_id', $this.relationshipOptions.lockFields) === -1) {
					$this.relationshipElements.relatedSelectedObjectTarget.append($("<a />", {'href': '#', 'class': 'btn btn-primary'}).html('Reselect').click(function() { $this.resetRelationship(); return false; }));
				}
			};
			$this.resetRelationship = function(datum) {
				$this.val('');
				$this.relationshipElements.relatedSelectedObjectTarget.hide();
				$this.showSelector();
			};

			selectorOptions.callback = $this.select;
			selectorOptions.context = $this.relationshipOptions.context;
			var $selector = $this.objectSelector(selectorOptions);
			if ($this.relationshipOptions.select) {
				$this.select($this, $this.relationshipOptions.select);
			} else {
				$.debug($this);
			}
		}

	});
});