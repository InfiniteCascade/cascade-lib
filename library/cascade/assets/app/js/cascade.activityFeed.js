function ActivityFeedItem(feed, item) {
	this.feed = feed;
	this.item = item;
	this.$element = $("<li />").html(this.getStory());
	this.$element.data('item', this);
	feed.add(this);
}

ActivityFeedItem.prototype.getStory = function() {
	if (this.story === undefined) {
		// @todo FIND ALL MATCHES FOR THE OBJECTS
		var objects = this.item.story.match(/\{\{(.+?)\}\}/);
		console.log(objects);
	}
	return this.story;
};

ActivityFeedItem.prototype.getSortKey = function() {
	return this.item.timestamp + '-' + this.item.primaryObject;
}

function ActivityFeedObject(feed, object) {
	this.feed = feed;
	this.object = object;
}

ActivityFeedObject.prototype.getRenderedObject = function() {
	if (this.$rendered === undefined) {
		if (this.feed.options.rich) {
			if (this.object.url !== undefined && this.object.url) {
				this.$rendered = $("<a />", {'href': this.getUrl()});
			} else {
				this.$rendered = $("<strong />");
			}
			this.$rendered.html(this.getNiceDescriptor());
		} else {
			this.$rendered = this.getNiceDescriptor();
		}
	}
	return this.$rendered;
};

ActivityFeedObject.prototype.getNiceDescriptor = function() {
	return this.object.descriptor;
}

ActivityFeedObject.prototype.getUrl = function() {
	return this.object.url;
}

function ActivityFeed($element, options) {
	var self = this;
	this.$element = $element;
	this.$thinking = $element.siblings('.activity-feed-thinking').first();
	this.components = {};
	this.objects = {};
	this.items = {};
	this.loadTimestamp = null;
	this.lastItem = null;
	this.lastItemTimestamp = null;
	this.options = jQuery.extend(true, {}, this.defaultOptions, options);
	this.init();
}

ActivityFeed.prototype.defaultOptions = {
	'ajax': {
		'url': '/app/activity'
	},
	'scope': null,
	'rich': true
};

ActivityFeed.prototype.DIRECTION_NEWER = '_newer';
ActivityFeed.prototype.DIRECTION_OLDER = '_older';

ActivityFeed.prototype.init = function() {
	var self = this;
	this.$element.hide();
	this.components.list = $("<ul />", {'class': 'ic-activity-feed'}).appendTo(this.$element);
	this.load(ActivityFeed.prototype.DIRECTION_OLDER, function() {
		self.$thinking.remove();
		self.$element.show();
	});
};

ActivityFeed.prototype.registerObject = function(id, object) {
	if (this.objects[id] === undefined) {
		this.objects[id] = new ActivityFeedObject(this, object);
	}
};

ActivityFeed.prototype.registerActivity = function(id, activity) {
	if (this.items[id] === undefined) {
		this.items[id] = new ActivityFeedItem(this, activity);
	}
};

ActivityFeed.prototype.getRenderedObjects = function() {
	var rendered = {};
	jQuery.each(this.objects, function(index, object) {
		rendered[index] = object.getRenderedObject();
	});
	return rendered;
};

ActivityFeed.prototype.add = function(item) {
	var items = $(this.components.list).find('li');
	if (items.length === 0) {
		// first item
		this.components.list.append(item.$element);
	} else {
		var inserted = false;
		$(items).each(function(index, eitem) {
			if ($(eitem).data('item').getSortKey() <= item.getSortKey()) {
				item.$element.insertBefore(eitem);
				inserted = true;
				return false;
			}
		});
		if (!inserted) {
			this.components.list.append(item.$element);
		}
	}
};

ActivityFeed.prototype.load = function(direction, callback) {
	var self = this;
	var ajaxSettings = this.options.ajax;

	ajaxSettings.complete = function() {
		if (callback !== undefined) {
			callback();
		}
	};
	ajaxSettings.success = function (response) {
		self.loadTimestamp = response.timestamp;
		jQuery.each(response.objects, function(id, object) {
			self.registerObject(id, object);
		});
		jQuery.each(response.activity, function(id, activity) {
			self.registerActivity(id, activity);
		});
		console.log(self);
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
		var handler = new ActivityFeed($(this), $(this).data('activity-feed'));
		$(this).data('activity-feed', '');
		$(this).data('ActivityFeed', handler);
	});
});