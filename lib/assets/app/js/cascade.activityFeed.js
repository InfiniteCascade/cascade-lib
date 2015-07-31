function ActivityFeedItem(feed, item) {
    this.feed = feed;
    this.item = item;
    this.$element = $('<li />', {'class': 'activity-item expandable expandable-delayed'});
    this.date = new Date(item.timestamp * 1000);
    var dateTitle = this.date.toLocaleString();
    this.$icons = $('<div />', {'class': 'activity-icons'}).appendTo(this.$element);
    this.$agentIcon = this.getAgentIcon().appendTo(this.$icons);
    this.$objectIcon = this.getObjectIcon().appendTo(this.$icons);
    this.$timeElement = $('<time />', {'class': 'relative-time', 'datetime': this.date.toISOString(), 'title': dateTitle}).appendTo(this.$element);
    this.$storyElement = $('<div />', {'class': 'activity-story smart-line'}).appendTo(this.$element).html(this.getStory()).data('smart-line', {'selector': 'a'});
    this.$element.data('item', this);
    feed.add(this);
    $preparer.fire(this.$element);
}

ActivityFeedItem.prototype.getObjectIcon = function() {
    var icon = false;
    if (this.item.primaryObject) {
        var object = this.feed.getObject(this.item.primaryObject);
        if (object) {
            icon = object.getIcon(this.item.primaryObject);
        }
    }
    if (!icon) {
        icon = $('<div />', {'class': 'fa fa-question'});
    }
    icon.addClass('activity-icon');
    return icon;
};

ActivityFeedItem.prototype.getAgentIcon = function() {
    var icon = false;
    if (this.item.agent) {
        var agent = this.feed.getObject(this.item.agent);
        if (agent) {
            icon = agent.getIcon(this.item.agent);
        }
    }
    if (!icon) {
        icon = $('<div />', {'class': 'fa fa-user'});
    }
    icon.addClass('activity-icon');
    return icon;
};

ActivityFeedItem.prototype.process = function(template) {
    var self = this;
    // var renderedObjects = this.feed.getRenderedObjects();
    template = template.replace(/\{\{([\w\-\:]+)\}\}/g,
    function(match, p1) {
        var renderedVariable = self.feed.getRenderedObject(this.item, p1);
        if (renderedVariable !== undefined && renderedVariable) {
            return renderedVariable; //renderedObjects[p1].outerHTML();
        }
        return null;
    }
	);

    template = template.replace(/\[\[([^\]]+)\]\]/g,
    function(match, p1) {
    return $('<em />').html(p1).outerHTML();
		}
	);

    return template;
};

ActivityFeedItem.prototype.getStory = function() {
    if (this.story === undefined) {
        this.story = this.process(this.item.story);
    }
    return this.story;
};

ActivityFeedItem.prototype.getSortKey = function() {
    return this.item.timestamp + '-' + this.item.primaryObject;
};

function ActivityFeedObject(feed, object) {
    this.feed = feed;
    this.object = object;
}

ActivityFeedObject.prototype.getIcon = function(variable) {
    if (this.$icon === undefined) {
        var parts = variable.split(':');
        var urlQuery = {};
        if (parts[1] !== undefined && this.objects[parts[1]] !== undefined) {
            urlQuery.h = parts[1];
        }
        if (this.object.url !== undefined && this.object.url) {
            this.$icon = $('<a />', {'href': this.getUrl(urlQuery)});
        } else {
            this.$icon = $('<div />');
        }
        this.$icon.attr('title', this.object.descriptor);
        var iconAttributes = {};
        if (this.object.icon !== undefined && this.object.icon) {
            iconAttributes = this.object.icon;
        } else if (this.object.type        &&
			jQuery.cascadeTypes.types[this.object.type] !== undefined) {
            iconAttributes = {'class': jQuery.cascadeTypes.types[this.object.type].icon};
        }

        if (iconAttributes.class !== undefined) {
            this.$icon.addClass(iconAttributes.class);
        } else if (iconAttributes.img !== undefined) {
            this.$icon.addClass('fa ic-icon-image');
            this.$icon.css('background-image', 'url(' + iconAttributes.img + ')');
        } else {
            this.$icon.addClass('fa fa-question');
        }
    }
    return this.$icon.clone();
};

ActivityFeedObject.prototype.getRenderedObject = function(urlQuery) {
    if (this.$rendered === undefined) {
        if (this.feed.options.rich) {
            if (this.object.url !== undefined && this.object.url) {
                this.$rendered = $('<a />', {'href': this.getUrl(urlQuery)});
            } else {
                this.$rendered = $('<strong />');
            }
            this.$rendered.html(this.getNiceDescriptor());
        } else {
            this.$rendered = $('<span />').html(this.getNiceDescriptor());
        }
    }
    return this.$rendered;
};

ActivityFeedObject.prototype.getNiceDescriptor = function() {
    return this.object.descriptor;
};

ActivityFeedObject.prototype.getUrl = function(urlQuery) {
    if (urlQuery === {}) {
        return this.object.url;
    }
    return this.object.url + '?' + jQuery.param(urlQuery);
};

function ActivityFeed($element, options) {
    var self = this;
    this.$element = $element;
    this.$thinking = $element.siblings('.activity-feed-thinking').first();
    this.components = {};
    this.objects = {};
    this.items = {};
    this.loading = false;
    this.loadTimestamp = null;
    this.lastItem = null;
    this.mostRecentItem = null;
    this.lastLoadMore = 0;
    this.options = jQuery.extend(true, {}, this.defaultOptions, options);
    this.init();
}
ActivityFeed.prototype.DIRECTION_NEWER = '_newer';
ActivityFeed.prototype.DIRECTION_OLDER = '_older';

ActivityFeed.prototype.defaultOptions = {
    'ajax': {
        'url': '/app/activity'
    },
    'object': null,
    'scope': null,
    'limit': null,
    'rich': true,
    'emptyMessage': 'There has been no activity.'
};

ActivityFeed.prototype.init = function() {
    var self = this;
    this.$element.hide();
    this.components.list = $('<ul />', {'class': 'ic-activity-feed'}).appendTo(this.$element);
    this.components.loadMore = $('<div />', {'class': 'ic-activity-load-more'}).appendTo(this.$element);
    this.load(ActivityFeed.prototype.DIRECTION_OLDER, function() {
        self.$thinking.hide();
        self.$element.show();
    });
    this.startCheckNewerTimer();
    this.startLoadMoreTimer();
    this.$element.on('remove', function() {
        self.stopCheckNewerTimer();
        self.stopLoadMoreTimer();
        self.items = {};
        self.object = {};
        self.components = {};
        delete self;
    });
};

ActivityFeed.prototype.getObject = function(variable) {
    var parts = variable.split(':');
    if (this.objects[parts[0]] !== undefined) {
        return this.objects[parts[0]];
    }
    return false;
};

ActivityFeed.prototype.getRenderedObject = function(item, variable) {
    var parts = variable.split(':');
    var urlQuery = {};
    if (parts[1] !== undefined && this.objects[parts[1]] !== undefined) {
        urlQuery.h = parts[1];
    }

    var object = this.getObject(variable);

    if (object) {
        var renderedObject = object.getRenderedObject(urlQuery);
        return renderedObject.outerHTML();
    }
    return false;
};

ActivityFeed.prototype.startCheckNewerTimer = function() {
    this.stopCheckNewerTimer();
    var self = this;
    timer.setInterval('activity-check-new', function() {
        self.load(ActivityFeed.prototype.DIRECTION_NEWER);
    }, 10000);
};

ActivityFeed.prototype.stopCheckNewerTimer = function() {
    timer.clear('activity-check-new');
};

ActivityFeed.prototype.startLoadMoreTimer = function() {
    this.stopLoadMoreTimer();
    var self = this;
    timer.setInterval('activity-load-more', function() {
        if (self.components.loadMore.isElementInViewport()        &&
			!self.loading        &&
			(((new Date().getTime()) / 1000) - self.lastLoadMore) > 2) {
            self.lastLoadMore = (new Date().getTime()) / 1000;
            self.$thinking.show();
            self.load(ActivityFeed.prototype.DIRECTION_OLDER, function(result) {
                self.$thinking.hide();
            });
        }
    }, 1000);
};

ActivityFeed.prototype.stopLoadMoreTimer = function() {
    timer.clear('activity-load-more');
};

ActivityFeed.prototype.updateEmptyActivity = function() {
    if (this.$emptyNotice === undefined) {
        this.$emptyNotice = $('<div />', {'class': 'ic-activity-empty'}).hide().html(this.options.emptyMessage).insertBefore(this.$element);
    }
    if (_.values(this.objects).length === 0) {
        this.$emptyNotice.show();
    } else {
        this.$emptyNotice.hide();
    }
};

ActivityFeed.prototype.registerObject = function(id, object) {
    if (this.objects[id] !== undefined) {
        delete this.objects[id];
    }
    this.objects[id] = new ActivityFeedObject(this, object);
};

ActivityFeed.prototype.registerActivity = function(id, activity) {
    if (this.items[id] === undefined) {
        this.items[id] = new ActivityFeedItem(this, activity);
        return true;
    }
    return false;
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
    if (this.loading) {
        if (callback !== undefined) {
            self.loading.on('complete', callback);
        }
        return;
    }

    ajaxSettings.complete = function(response) {
        if (callback !== undefined) {
            callback(response);
        }
        self.loading = false;
    };
    ajaxSettings.success = function(response) {
        if (response.objects === undefined) {
            return true;
        }
        self.loadTimestamp = response.timestamp;

        if (response.lastItem &&
			(!self.lastItem ||
			self.lastItem > response.lastItem)) {
            self.lastItem = response.lastItem;
            console.log(['update lastItem', response.lastItem]);
        }
        if (response.mostRecentItem &&
			(!self.mostRecentItem ||
			self.mostRecentItem < response.mostRecentItem)) {
            self.mostRecentItem = response.mostRecentItem;
            console.log(['update mostRecentItem', response.mostRecentItem]);
        }
        jQuery.each(response.objects, function(id, object) {
            self.registerObject(id, object);
        });
        var found = false;
        jQuery.each(response.activity, function(id, activity) {
            if (self.registerActivity(id, activity)) {
                found = true;
            }
        });
        if (!found && response.direction === '_older') {
            self.stopLoadMoreTimer();
        }
        self.updateEmptyActivity();
    };

    ajaxSettings.data = {
        'scope': self.options.scope,
        'object': self.options.object,
        'direction': direction,
        'lastItem': self.lastItem,
        'mostRecentItem': self.mostRecentItem,
        'loadTimestamp': self.loadTimestamp,
        'limit': self.options.limit
    };
    ajaxSettings.dataType = 'json';
    ajaxSettings.type = 'POST';
    this.loading = jQuery.ajax(ajaxSettings);
};

$preparer.add(function(context) {
    $('[data-activity-feed]', context).each(function() {
        var $this = $(this);
        var handler = new ActivityFeed($(this), $(this).data('activity-feed'));
        $(this).data('activity-feed', '');
        $(this).data('ActivityFeed', handler);
    });
});
