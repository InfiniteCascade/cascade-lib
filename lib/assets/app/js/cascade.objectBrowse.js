function ObjectBrowser (parent, options) {
    CanisBrowser.call(this, parent, options);
}

ObjectBrowser.prototype = Object.create(CanisBrowser.prototype);

(function($) {
    $.fn.objectBrowse = function(options) {
        var self = this;
        if (self.objectBrowseObject === undefined) {
            self.objectBrowseObject = new ObjectBrowser(self, options);
        }

        return self.objectBrowseObject;
    };
}(jQuery));
