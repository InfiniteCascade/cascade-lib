function editInPlaceHandler($element, options) {
	var self = this;
	this.$element = $element;
	this.$thinking = $("<div />", {'class': 'ic-eip-thinking'}).matchPositionSize($element, {height: 22, width: 22}).insertAfter(this.$element).hide();

	this.options = jQuery.extend(true, {}, this.defaultOptions, options);
	this.$element.on('change', null, self, this.triggerChange);
	if (this.options.currentValue === undefined) {
		this.currentValue = this.getValue();
	} else {
		this.currentValue = this.options.currentValue;
	}
}


editInPlaceHandler.prototype.defaultOptions = {
	'ajax': {
		'url': '/object/update-field'
	}
};

editInPlaceHandler.prototype.getValue = function() {
	if (this.$element.is('[type="checkbox"]')) {
		if (this.$element.is(':checked')) {
			return this.$element.attr('value');
		} else {
			if (this.$element.attr('uncheckedValue') !== undefined) {
				return this.$element.attr('uncheckedValue');
			} else {
				return null;
			}
		}
	}
	return $element.value();
};


editInPlaceHandler.prototype.setValue = function(value) {
	if (this.$element.is('[type="checkbox"]')) {
		var checkedValue = this.$element.attr('value');
		var currentValue = this.getValue();
		if (value !== checkedValue) {
			this.$element.prop('checked', false);
		} else {
			this.$element.prop('checked', true);
		}
	} else {
		$element.value(value);
	}
	this.currentValue = value;
	return true;
};

editInPlaceHandler.prototype.triggerChange = function(e) {
	var self = e.data;
	var $element = self.$element;
	var value = self.getValue();
	if (value !== self.currentValue) {
		self.handleUpdate(value);
	}
};

editInPlaceHandler.prototype.handleUpdate = function(value) {
	var self = this;
	self.previousValue = self.currentValue;
	self.currentValue = value;
	this.$thinking.show();
	var ajaxSettings = this.options.ajax;
	ajaxSettings.complete = function() {
		self.$thinking.hide();
	};
	ajaxSettings.error = function () {
		self.setValue(self.previousValue);
		$.debug("boom: "+ self.previousValue);
	};
	ajaxSettings.success = function (response) {
		if (response.error) {
			self.setValue(self.previousValue);
			$.debug("boom: "+ self.previousValue);
		}
	};
	ajaxSettings.data = this.options.data || {};
	ajaxSettings.data.value = value;
	ajaxSettings.dataType = 'json';
	ajaxSettings.type = 'POST';
	jQuery.ajax(ajaxSettings);
};

$preparer.add(function(context) {
	$("[data-edit-in-place]", context).each(function() {
		var $this = $(this);
		var handler = new editInPlaceHandler($(this), $(this).data('edit-in-place'));
		$(this).data('editInPlaceHandler', handler);
	});
});