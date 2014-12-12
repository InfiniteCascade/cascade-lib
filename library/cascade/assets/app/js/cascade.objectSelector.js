function ObjectSelector (parent, options) {
   InfiniteSelector.call(this, parent, options);
}

ObjectSelector.prototype = Object.create(InfiniteSelector.prototype);

(function ($) { 
   $.fn.objectSelector = function (options) {
         var $this = this;
         if ($this.objectSelectorObject === undefined) {
            $this.objectSelectorObject = new ObjectSelector($this, options);
         }

         return $this.objectSelectorObject;
   };
}(jQuery));