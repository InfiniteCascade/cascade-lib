function ObjectSearch(parent, options) {
    CanisSearch.call(this, parent, options);
}

ObjectSearch.prototype = Object.create(CanisSearch.prototype);

(function($) {
    $.fn.objectSearch = function(options) {
        var self = this;
        if (self.objectSearchObject === undefined) {
            self.objectSearchObject = new ObjectSearch(self, options);
        }

        return self.objectSearchObject;
    };
}(jQuery));
