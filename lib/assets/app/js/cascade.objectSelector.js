function ObjectSelector (parent, options) {
   CanisSelector.call(this, parent, options);
}

ObjectSelector.prototype = Object.create(CanisSelector.prototype);

(function ($) { 
   $.fn.objectSelector = function (options) {
         var $this = this;
         if ($this.objectSelectorObject === undefined) {
            $this.objectSelectorObject = new ObjectSelector($this, options);
         }

         return $this.objectSelectorObject;
   };
}(jQuery));