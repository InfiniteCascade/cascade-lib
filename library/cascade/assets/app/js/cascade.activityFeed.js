function ActivityFeedItem(feed, item) {
	this.feed = feed;
	this.item = item;
	this.$element = $("<li />").html(this.getStory());
	this.$element.data('item', this);
	feed.add(this);
}
ActivityFeedItem.prototype.process = function(template) {
	var self = this;
	// var renderedObjects = this.feed.getRenderedObjects();
    return template.replace(/\{\{([\w\-\:]+)\}\}/g,
	    function(match, p1) {
	    	var renderedVariable = self.feed.getObject(this.item, p1);
	    	if (renderedVariable !== undefined && renderedVariable) {
	    		return renderedVariable; //renderedObjects[p1].outerHTML();
	    	}
	    	return null;
		}
	);
};


ActivityFeedItem.prototype.getStory = function() {
	if (this.story === undefined) {
		this.story = this.process(this.item.story);
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


ActivityFeedObject.prototype.getRenderedObject = function(urlQuery) {
	if (this.$rendered === undefined) {
		if (this.feed.options.rich) {
			if (this.object.url !== undefined && this.object.url) {
				this.$rendered = $("<a />", {'href': this.getUrl(urlQuery)});
			} else {
				this.$rendered = $("<strong />");
			}
			this.$rendered.html(this.getNiceDescriptor());
		} else {
			this.$rendered = $("<span />").html(this.getNiceDescriptor());
		}
	}
	return this.$rendered;
};

ActivityFeedObject.prototype.getNiceDescriptor = function() {
	return this.object.descriptor;
}

ActivityFeedObject.prototype.getUrl = function(urlQuery) {
	if (urlQuery === {}) {
		return this.object.url;
	}
	return this.object.url +"?" + jQuery.param(urlQuery);
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

ActivityFeed.prototype.getObject = function(item, variable) {
	var parts = variable.split(':');
	var urlQuery = {};
	if (parts[1] !== undefined && this.objects[parts[1]] !== undefined) {
		urlQuery['r'] = parts[1];	
	}
	if (this.objects[parts[0]] !== undefined) {
		var object = this.objects[parts[0]].getRenderedObject(urlQuery);
		return object.outerHTML();
	}
	return false;
};
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