function ObjectSelector (parent, options) {
    CanisSelector.call(this, parent, options);
}

ObjectSelector.prototype = Object.create(CanisSelector.prototype);

(function($) {
    $.fn.objectSelector = function(options) {
        var self = this;
        if (self.objectSelectorObject === undefined) {
            self.objectSelectorObject = new ObjectSelector(self, options);
        }

        return self.objectSelectorObject;
    };
}(jQuery));
