function activityFeed($element, options) {
	var self = this;
	this.$element = $element;
	this.$thinking = $element.siblings('.activity-feed-thinking').first();
	this.components = {};
	this.loadTimestamp = null;
	this.lastItem = null;
	this.lastItemTimestamp = null;
	this.options = jQuery.extend(true, {}, this.defaultOptions, options);
	this.init();
}

activityFeed.prototype.defaultOptions = {
	'ajax': {
		'url': '/app/activity'
	},
	'scope': null
};

activityFeed.prototype.DIRECTION_NEWER = '_newer';
activityFeed.prototype.DIRECTION_OLDER = '_older';

activityFeed.prototype.init = function() {
	var self = this;
	this.$element.hide();
	this.components.list = $("<ul />", {'class': 'ic-activity-feed'});
	this.load(activityFeed.prototype.DIRECTION_OLDER, function() {
		self.$thinking.remove();
		self.$element.show();
	});
};

activityFeed.prototype.load = function(direction, callback) {
	var self = this;
	var ajaxSettings = this.options.ajax;

	ajaxSettings.complete = function() {
		if (callback !== undefined) {
			callback();
		}
	};
	ajaxSettings.success = function (response) {
		self.loadTimestamp = response.timestamp;

		$.debug(response);	
	};
	ajaxSettings.data = {
		'scope': self.options.scope,
		'direction': direction,
		'lastItem': self.lastItem,
		'lastItemTimestamp': self.lastItemTimestamp,
		'loadTimestamp': self.loadTimestamp
	};
	ajaxSettings.dataType = 'json';
	ajaxSettings.type = 'POST';
	jQuery.ajax(ajaxSettings);
};


$preparer.add(function(context) {
	$("[data-activity-feed]", context).each(function() {
		var $this = $(this);
		var handler = new activityFeed($(this), $(this).data('activity-feed'));
		$(this).data('activity-feed', '');
		$(this).data('activityFeed', handler);
	});
});